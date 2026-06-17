<?php

namespace App\Services\Trading;

use App\Models\Trading\Achievement;
use App\Models\Trading\LeaderboardSnapshot;
use App\Models\Trading\TournamentParticipant;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingNotification;
use App\Models\Trading\TradingSetting;
use App\Models\Trading\Wallet;
use App\Models\Trading\WalletEntry;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    /**
     * Hard-reset a user's account: permanently delete ALL of their money/wallet
     * data — wallet, ledger, trades, achievements, notifications, leaderboard
     * snapshots and tournament entries — then create a fresh wallet funded with
     * the default starting balance. The user account, profile and education
     * progress are kept. This is irreversible.
     *
     * @return array{deleted: array<string,int>, wallet: Wallet}
     */
    public function wipeAndReset(User $user): array
    {
        $deleted = DB::transaction(function () use ($user) {
            $counts = [
                // Practice (demo) trades only — real-money live trades are never wiped.
                'trades' => Trade::where('user_id', $user->id)->where('account', 'demo')->count(),
                'ledger_entries' => WalletEntry::whereIn('wallet_id',
                    Wallet::where('user_id', $user->id)->pluck('id'))->count(),
                'achievements' => Achievement::where('user_id', $user->id)->count(),
                'notifications' => TradingNotification::where('user_id', $user->id)->count(),
                'leaderboard' => LeaderboardSnapshot::where('user_id', $user->id)->count(),
                'tournaments' => TournamentParticipant::where('user_id', $user->id)->count(),
            ];

            // Only practice trades are removed — this is a PRACTICE reset and must
            // never delete live trades or affect the real-money Live Account.
            // trade_id on ledger entries is nullOnDelete; wallet_id cascades — so
            // deleting trades then the wallet is FK-safe.
            Trade::where('user_id', $user->id)->where('account', 'demo')->delete();
            Achievement::where('user_id', $user->id)->delete();
            TradingNotification::where('user_id', $user->id)->delete();
            LeaderboardSnapshot::where('user_id', $user->id)->delete();
            TournamentParticipant::where('user_id', $user->id)->delete();

            // Deleting the wallet cascade-deletes any remaining ledger entries.
            Wallet::where('user_id', $user->id)->delete();

            return $counts;
        });

        // Fresh wallet + starting balance (outside the wipe transaction).
        $wallet = $this->grantStartingBalance($user->id);

        // Cached leaderboards now include stale data for this user.
        foreach (['weekly', 'monthly', 'all_time'] as $period) {
            Cache::forget("leaderboard:{$period}");
        }

        return ['deleted' => $deleted, 'wallet' => $wallet];
    }

    /**
     * Get or create a wallet for the given user, seeding the starting balance on first creation.
     */
    public function walletFor(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency_label' => 'USD']
        );
    }

    /**
     * Grant the configured starting practice balance to a user.
     * No-op if the wallet already has entries (i.e. already funded).
     */
    public function grantStartingBalance(int $userId): Wallet
    {
        return DB::transaction(function () use ($userId) {
            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency_label' => 'USD']
            );

            if ($wallet->entries()->exists()) {
                return $wallet;
            }

            $amount = (int) TradingSetting::get('default_start_balance', 1000);

            return $this->credit($wallet, $amount, 'topup', ['reason' => 'starting_balance']);
        });
    }

    /**
     * Credit (add) virtual units to a wallet.
     *
     * @throws RuntimeException
     */
    public function credit(Wallet $wallet, int $amount, string $type, array $meta = [], ?int $tradeId = null): Wallet
    {
        if ($amount <= 0) {
            throw new RuntimeException("Credit amount must be positive, got {$amount}.");
        }

        return DB::transaction(function () use ($wallet, $amount, $type, $meta, $tradeId) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $newBalance = $wallet->balance + $amount;

            WalletEntry::create([
                'wallet_id' => $wallet->id,
                'trade_id' => $tradeId,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);

            $wallet->balance = $newBalance;
            $wallet->save();

            return $wallet->fresh();
        });
    }

    /**
     * Debit (subtract) virtual units from a wallet.
     *
     * @throws RuntimeException if the resulting balance would be negative.
     */
    public function debit(Wallet $wallet, int $amount, string $type, array $meta = [], ?int $tradeId = null): Wallet
    {
        if ($amount <= 0) {
            throw new RuntimeException("Debit amount must be positive, got {$amount}.");
        }

        return DB::transaction(function () use ($wallet, $amount, $type, $meta, $tradeId) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            if ($wallet->balance < $amount) {
                throw new RuntimeException(
                    "Insufficient balance: has {$wallet->balance}, needs {$amount}."
                );
            }

            $newBalance = $wallet->balance - $amount;

            WalletEntry::create([
                'wallet_id' => $wallet->id,
                'trade_id' => $tradeId,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);

            $wallet->balance = $newBalance;
            $wallet->save();

            return $wallet->fresh();
        });
    }

    /**
     * Compute balance directly from the ledger sum and verify it matches the cached column.
     * Returns true if consistent.
     */
    public function verifyLedgerConsistency(Wallet $wallet): bool
    {
        $ledgerSum = (int) $wallet->entries()->sum('amount');

        return $ledgerSum === (int) $wallet->balance;
    }
}
