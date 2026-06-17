<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\DepositRequest;
use App\Models\Trading\LiveWallet;
use App\Models\Trading\TradingSetting;
use App\Models\Trading\WithdrawalRequest;
use App\Services\Trading\LiveAccountNotifier;
use App\Services\Trading\LiveWalletService;
use Illuminate\Http\Request;

/**
 * Admin control room for the Live Account: review deposit/withdrawal requests,
 * oversee every funded wallet, and configure payment details + the daily rate.
 */
class LiveController extends Controller
{
    public function __construct(
        private readonly LiveWalletService $wallets,
        private readonly LiveAccountNotifier $notifier,
    ) {}

    /** Live Account overview: totals + pending counts. */
    public function overview()
    {
        $currency = $this->wallets->currency();

        return view('admin.trading.live.overview', [
            'currency' => $currency,
            'totalBalance' => (int) LiveWallet::sum('balance'),
            'totalDeposited' => (int) LiveWallet::sum('total_deposited'),
            'totalWithdrawn' => (int) LiveWallet::sum('total_withdrawn'),
            'totalProfit' => (int) LiveWallet::sum('total_profit'),
            'walletCount' => LiveWallet::where('balance', '>', 0)->count(),
            'pendingDeposits' => DepositRequest::pending()->count(),
            'pendingWithdrawals' => WithdrawalRequest::pending()->count(),
            'lastDistribution' => \App\Models\Trading\LiveDistribution::latest()->first(),
            'recentDeposits' => DepositRequest::with('user')->latest()->limit(6)->get(),
            'recentWithdrawals' => WithdrawalRequest::with('user')->latest()->limit(6)->get(),
        ]);
    }

    // ── Deposits ─────────────────────────────────────────────────────────────

    public function deposits(Request $request)
    {
        $status = $request->get('status', 'pending');
        $query = DepositRequest::with(['user', 'reviewer'])->latest();
        if (in_array($status, ['pending', 'approved', 'declined'], true)) {
            $query->where('status', $status);
        }

        return view('admin.trading.live.deposits', [
            'requests' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'currency' => $this->wallets->currency(),
            'counts' => $this->statusCounts(DepositRequest::class),
        ]);
    }

    public function approveDeposit(Request $request, DepositRequest $deposit)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:300']]);
        try {
            $deposit = $this->wallets->approveDeposit($deposit, $request->user(), $data['admin_note'] ?? null);
            $this->notifier->depositApproved($deposit->load('user'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Deposit approved and credited to the student\'s Live Account.');
    }

    public function declineDeposit(Request $request, DepositRequest $deposit)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:300']]);
        try {
            $deposit = $this->wallets->declineDeposit($deposit, $request->user(), $data['admin_note'] ?? null);
            $this->notifier->depositDeclined($deposit->load('user'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Deposit request declined. The student has been notified.');
    }

    // ── Withdrawals ──────────────────────────────────────────────────────────

    public function withdrawals(Request $request)
    {
        $status = $request->get('status', 'pending');
        $query = WithdrawalRequest::with(['user', 'reviewer'])->latest();
        if (in_array($status, ['pending', 'approved', 'declined'], true)) {
            $query->where('status', $status);
        }

        return view('admin.trading.live.withdrawals', [
            'requests' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'currency' => $this->wallets->currency(),
            'counts' => $this->statusCounts(WithdrawalRequest::class),
        ]);
    }

    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawal)
    {
        $data = $request->validate([
            'payout_reference' => ['nullable', 'string', 'max:120'],
            'admin_note' => ['nullable', 'string', 'max:300'],
        ]);
        try {
            $withdrawal = $this->wallets->approveWithdrawal($withdrawal, $request->user(), $data['payout_reference'] ?? null, $data['admin_note'] ?? null);
            $this->notifier->withdrawalApproved($withdrawal->load('user'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Withdrawal approved and debited. The student has been notified.');
    }

    public function declineWithdrawal(Request $request, WithdrawalRequest $withdrawal)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:300']]);
        try {
            $withdrawal = $this->wallets->declineWithdrawal($withdrawal, $request->user(), $data['admin_note'] ?? null);
            $this->notifier->withdrawalDeclined($withdrawal->load('user'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Withdrawal request declined. The student\'s balance is unchanged.');
    }

    // ── Accounts ─────────────────────────────────────────────────────────────

    public function accounts(Request $request)
    {
        $query = LiveWallet::with('user')->orderByDesc('balance');
        if ($search = $request->get('search')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return view('admin.trading.live.accounts', [
            'wallets' => $query->paginate(25)->withQueryString(),
            'currency' => $this->wallets->currency(),
        ]);
    }

    public function account(LiveWallet $wallet)
    {
        $wallet->load('user');
        $transactions = $wallet->transactions()->latest('created_at')->latest('id')->paginate(25);

        return view('admin.trading.live.account', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'available' => $this->wallets->availableBalance($wallet),
            'currency' => $this->wallets->currency(),
            'ledgerOk' => $this->wallets->verifyLedger($wallet),
        ]);
    }

    // ── Settings ─────────────────────────────────────────────────────────────

    private array $keys = [
        'live_account_enabled', 'live_account_currency',
        'live_account_min_deposit', 'live_account_min_withdrawal',
        'live_account_crypto_address', 'live_account_crypto_network',
        'live_account_payment_link', 'live_account_payment_instructions',
    ];

    public function settings()
    {
        $settings = [];
        foreach ($this->keys as $key) {
            $settings[$key] = TradingSetting::get($key, '');
        }

        return view('admin.trading.live.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'live_account_enabled' => ['required', 'in:true,false'],
            'live_account_currency' => ['required', 'string', 'max:8'],
            'live_account_min_deposit' => ['required', 'integer', 'min:0'],
            'live_account_min_withdrawal' => ['required', 'integer', 'min:0'],
            'live_account_crypto_address' => ['nullable', 'string', 'max:191'],
            'live_account_crypto_network' => ['required', 'string', 'max:40'],
            'live_account_payment_link' => ['nullable', 'url', 'max:255'],
            'live_account_payment_instructions' => ['required', 'string', 'max:4000'],
        ]);

        foreach ($data as $key => $value) {
            TradingSetting::set($key, (string) ($value ?? ''));
        }

        return back()->with('success', 'Live Account settings saved.');
    }

    /** @param  class-string  $model */
    private function statusCounts(string $model): array
    {
        return [
            'pending' => $model::where('status', 'pending')->count(),
            'approved' => $model::where('status', 'approved')->count(),
            'declined' => $model::where('status', 'declined')->count(),
        ];
    }
}
