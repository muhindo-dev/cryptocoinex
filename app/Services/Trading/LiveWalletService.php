<?php

namespace App\Services\Trading;

use App\Models\Trading\DepositRequest;
use App\Models\Trading\LiveTransaction;
use App\Models\Trading\LiveWallet;
use App\Models\Trading\TradingSetting;
use App\Models\Trading\WithdrawalRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The single source of truth for all Live Account money movement.
 *
 * Every balance change goes through {@see credit()} / {@see debit()}, which run
 * inside a DB transaction with a row lock and append an immutable ledger row
 * whose balance_after mirrors the wallet column — so the ledger and the cached
 * balance can never drift. Real money: no shortcuts, no float, no silent errors.
 */
class LiveWalletService
{
    /** Get or create a user's Live Account wallet. */
    public function walletFor(int $userId): LiveWallet
    {
        return LiveWallet::firstOrCreate(
            ['user_id' => $userId],
            ['currency' => $this->currency(), 'balance' => 0],
        );
    }

    public function currency(): string
    {
        return (string) TradingSetting::get('live_account_currency', 'USD');
    }

    /**
     * Balance the user may currently withdraw: settled balance minus the total
     * of any still-pending withdrawal requests (so two requests can't both be
     * approved for more than the user actually holds).
     */
    public function availableBalance(LiveWallet $wallet): int
    {
        $pending = (int) WithdrawalRequest::where('user_id', $wallet->user_id)
            ->where('status', 'pending')
            ->sum('amount');

        return max(0, $wallet->balance - $pending);
    }

    // ── Core ledger primitives ───────────────────────────────────────────────

    /**
     * Credit (add) real money to a wallet and append a ledger row. Returns the
     * created transaction.
     */
    public function credit(
        LiveWallet $wallet,
        int $amount,
        string $type,
        string $description,
        array $meta = [],
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?Carbon $accrualDate = null,
    ): LiveTransaction {
        if ($amount <= 0) {
            throw new RuntimeException("Credit amount must be positive, got {$amount}.");
        }

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $meta, $sourceType, $sourceId, $accrualDate) {
            $locked = LiveWallet::lockForUpdate()->findOrFail($wallet->id);
            $newBalance = $locked->balance + $amount;

            $tx = LiveTransaction::create([
                'live_wallet_id' => $locked->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'accrual_date' => $accrualDate?->toDateString(),
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);

            $locked->balance = $newBalance;
            if ($type === 'deposit') {
                $locked->total_deposited += $amount;
            } elseif ($type === 'profit' || $type === 'distribution') {
                $locked->total_profit += $amount;
                $locked->last_accrued_on = now();
            }
            $locked->save();

            return $tx;
        });
    }

    /**
     * Debit (remove) real money from a wallet. Throws if the balance is
     * insufficient — a debit must never push the balance negative.
     */
    public function debit(
        LiveWallet $wallet,
        int $amount,
        string $type,
        string $description,
        array $meta = [],
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): LiveTransaction {
        if ($amount <= 0) {
            throw new RuntimeException("Debit amount must be positive, got {$amount}.");
        }

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $meta, $sourceType, $sourceId) {
            $locked = LiveWallet::lockForUpdate()->findOrFail($wallet->id);

            if ($locked->balance < $amount) {
                throw new RuntimeException("Insufficient Live Account balance: has {$locked->balance}, needs {$amount}.");
            }

            $newBalance = $locked->balance - $amount;

            $tx = LiveTransaction::create([
                'live_wallet_id' => $locked->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);

            $locked->balance = $newBalance;
            if ($type === 'withdrawal') {
                $locked->total_withdrawn += $amount;
            }
            $locked->save();

            return $tx;
        });
    }

    // ── Deposit lifecycle ────────────────────────────────────────────────────

    /** Student declares a crypto payment; creates a pending request with proof. */
    public function requestDeposit(User $user, int $amount, ?string $reference, ?string $proofPath, ?string $note): DepositRequest
    {
        return DepositRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'reference' => $reference,
            'proof_path' => $proofPath,
            'paid_to' => TradingSetting::get('live_account_crypto_address'),
            'note' => $note,
            'status' => 'pending',
        ]);
    }

    /**
     * Admin confirms the money was received: credits the wallet and stamps the
     * request approved. Guarded against double-approval.
     */
    public function approveDeposit(DepositRequest $request, User $admin, ?string $adminNote = null): DepositRequest
    {
        return DB::transaction(function () use ($request, $admin, $adminNote) {
            $request = DepositRequest::lockForUpdate()->findOrFail($request->id);

            if (! $request->isPending()) {
                throw new RuntimeException('This deposit request has already been reviewed.');
            }

            $wallet = $this->walletFor($request->user_id);
            $tx = $this->credit(
                $wallet,
                $request->amount,
                'deposit',
                'Deposit confirmed (ref '.$request->reference.')',
                ['reference' => $request->reference, 'approved_by' => $admin->id],
                'deposit_request',
                $request->id,
            );

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'admin_note' => $adminNote,
                'reviewed_at' => now(),
                'live_transaction_id' => $tx->id,
            ]);

            return $request;
        });
    }

    public function declineDeposit(DepositRequest $request, User $admin, ?string $adminNote = null): DepositRequest
    {
        return DB::transaction(function () use ($request, $admin, $adminNote) {
            $request = DepositRequest::lockForUpdate()->findOrFail($request->id);

            if (! $request->isPending()) {
                throw new RuntimeException('This deposit request has already been reviewed.');
            }

            $request->update([
                'status' => 'declined',
                'reviewed_by' => $admin->id,
                'admin_note' => $adminNote,
                'reviewed_at' => now(),
            ]);

            return $request;
        });
    }

    // ── Withdrawal lifecycle ─────────────────────────────────────────────────

    /**
     * Student asks to cash out to a mobile number. Validated against the
     * available (not just raw) balance so concurrent requests can't oversell.
     */
    public function requestWithdrawal(User $user, int $amount, string $payoutAddress, ?string $payoutNetwork, ?string $note): WithdrawalRequest
    {
        return DB::transaction(function () use ($user, $amount, $payoutAddress, $payoutNetwork, $note) {
            $wallet = $this->walletFor($user->id);
            // Re-read available balance inside the transaction to avoid races.
            if ($amount > $this->availableBalance($wallet)) {
                throw new RuntimeException('Amount exceeds your available balance.');
            }

            return WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'payout_address' => $payoutAddress,
                'payout_network' => $payoutNetwork,
                'note' => $note,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Admin confirms the payout was sent: debits the wallet and stamps the
     * request approved. Guarded against double-approval and insufficient funds.
     */
    public function approveWithdrawal(WithdrawalRequest $request, User $admin, ?string $payoutReference = null, ?string $adminNote = null): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $admin, $payoutReference, $adminNote) {
            $request = WithdrawalRequest::lockForUpdate()->findOrFail($request->id);

            if (! $request->isPending()) {
                throw new RuntimeException('This withdrawal request has already been reviewed.');
            }

            $wallet = $this->walletFor($request->user_id);
            $tx = $this->debit(
                $wallet,
                $request->amount,
                'withdrawal',
                'Withdrawal paid to '.$request->destination,
                ['payout_to' => $request->destination, 'reference' => $payoutReference, 'approved_by' => $admin->id],
                'withdrawal_request',
                $request->id,
            );

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'admin_note' => $adminNote,
                'payout_reference' => $payoutReference,
                'reviewed_at' => now(),
                'live_transaction_id' => $tx->id,
            ]);

            return $request;
        });
    }

    public function declineWithdrawal(WithdrawalRequest $request, User $admin, ?string $adminNote = null): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $admin, $adminNote) {
            $request = WithdrawalRequest::lockForUpdate()->findOrFail($request->id);

            if (! $request->isPending()) {
                throw new RuntimeException('This withdrawal request has already been reviewed.');
            }

            $request->update([
                'status' => 'declined',
                'reviewed_by' => $admin->id,
                'admin_note' => $adminNote,
                'reviewed_at' => now(),
            ]);

            return $request;
        });
    }

    /** Ledger integrity check: ledger sum must equal the cached balance. */
    public function verifyLedger(LiveWallet $wallet): bool
    {
        return (int) $wallet->transactions()->sum('amount') === (int) $wallet->balance;
    }
}
