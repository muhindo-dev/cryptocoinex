<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\TradingSetting;
use App\Services\Trading\LiveAccountNotifier;
use App\Services\Trading\LiveWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The student-facing Live Account: real-money balance, deposit & withdrawal
 * requests, daily managed-trading returns and a full transaction history.
 */
class LiveAccountController extends Controller
{
    public function __construct(
        private readonly LiveWalletService $wallets,
        private readonly LiveAccountNotifier $notifier,
    ) {}

    private function ensureEnabled(): void
    {
        abort_unless(TradingSetting::get('live_account_enabled', 'true') === 'true', 404);
    }

    /**
     * Block a real-money action and bounce to verification when the user isn't
     * KYC-approved. Returns a redirect to act on, or null when cleared.
     */
    private function kycGate(): ?\Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->requiresKyc()) {
            return redirect()->route('trade.kyc')
                ->with('error', 'Please verify your identity before using real-money features.');
        }

        return null;
    }

    /** GET /trade/live — the Live Account dashboard. */
    public function index()
    {
        $this->ensureEnabled();
        $user = Auth::user();
        $wallet = $this->wallets->walletFor($user->id);

        $recent = $wallet->transactions()->latest('created_at')->latest('id')->limit(8)->get();
        $pendingDeposits = $user->depositRequests()->pending()->latest()->get();
        $pendingWithdrawals = $user->withdrawalRequests()->pending()->latest()->get();

        return view('trading.live.index', [
            'wallet' => $wallet,
            'available' => $this->wallets->availableBalance($wallet),
            'recent' => $recent,
            'pendingDeposits' => $pendingDeposits,
            'pendingWithdrawals' => $pendingWithdrawals,
            'instructions' => TradingSetting::get('live_account_payment_instructions'),
            'cryptoAddress' => TradingSetting::get('live_account_crypto_address'),
            'cryptoNetwork' => TradingSetting::get('live_account_crypto_network', 'USD (TRC20)'),
            'paymentLink' => TradingSetting::get('live_account_payment_link'),
        ]);
    }

    /** GET /trade/live/transactions — full ledger. */
    public function transactions()
    {
        $this->ensureEnabled();
        $wallet = $this->wallets->walletFor(Auth::id());
        $transactions = $wallet->transactions()->latest('created_at')->latest('id')->paginate(30);

        return view('trading.live.transactions', compact('wallet', 'transactions'));
    }

    /** GET /trade/live/deposit — deposit request form. */
    public function deposit()
    {
        $this->ensureEnabled();
        if ($r = $this->kycGate()) {
            return $r;
        }

        return view('trading.live.deposit', [
            'min' => (int) TradingSetting::get('live_account_min_deposit', 0),
            'currency' => $this->wallets->currency(),
            'instructions' => TradingSetting::get('live_account_payment_instructions'),
            'cryptoAddress' => TradingSetting::get('live_account_crypto_address'),
            'cryptoNetwork' => TradingSetting::get('live_account_crypto_network', 'USD (TRC20)'),
            'paymentLink' => TradingSetting::get('live_account_payment_link'),
        ]);
    }

    /** POST /trade/live/deposit — crypto deposit declaration + proof screenshot. */
    public function storeDeposit(Request $request)
    {
        $this->ensureEnabled();
        if ($r = $this->kycGate()) {
            return $r;
        }
        $min = (int) TradingSetting::get('live_account_min_deposit', 0);

        $data = $request->validate([
            'amount' => ['required', 'integer', "min:{$min}", 'max:100000000'],
            'reference' => ['nullable', 'string', 'max:191'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5 MB
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'proof.required' => 'Please upload a screenshot of your payment.',
        ], ['amount' => 'amount (USD)', 'reference' => 'transaction reference']);

        // Store the proof screenshot on the public disk.
        $proofPath = $request->file('proof')->store('deposit_proofs', 'public');

        $req = $this->wallets->requestDeposit(
            Auth::user(), (int) $data['amount'], $data['reference'] ?? null,
            $proofPath, $data['note'] ?? null,
        );

        $this->notifier->depositRequested($req->load('user'));

        return redirect()->route('trade.live')
            ->with('success', 'Deposit request submitted with your payment proof. We\'ll verify it and credit your account shortly — you\'ll get an email once it\'s approved.');
    }

    /** GET /trade/live/withdraw — withdrawal request form. */
    public function withdraw()
    {
        $this->ensureEnabled();
        if ($r = $this->kycGate()) {
            return $r;
        }
        $wallet = $this->wallets->walletFor(Auth::id());

        return view('trading.live.withdraw', [
            'wallet' => $wallet,
            'available' => $this->wallets->availableBalance($wallet),
            'min' => (int) TradingSetting::get('live_account_min_withdrawal', 0),
            'currency' => $this->wallets->currency(),
            'network' => TradingSetting::get('live_account_crypto_network', 'USD (TRC20)'),
        ]);
    }

    /** POST /trade/live/withdraw — payout to a crypto wallet address. */
    public function storeWithdrawal(Request $request)
    {
        $this->ensureEnabled();
        if ($r = $this->kycGate()) {
            return $r;
        }
        $wallet = $this->wallets->walletFor(Auth::id());
        $available = $this->wallets->availableBalance($wallet);
        $min = (int) TradingSetting::get('live_account_min_withdrawal', 0);

        $data = $request->validate([
            'amount' => ['required', 'integer', "min:{$min}", "max:{$available}"],
            'payout_address' => ['required', 'string', 'min:20', 'max:191'],
            'payout_network' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.max' => "You can withdraw at most :max — that's your available balance.",
        ], ['amount' => 'amount (USD)', 'payout_address' => 'wallet address']);

        try {
            $req = $this->wallets->requestWithdrawal(
                Auth::user(), (int) $data['amount'], $data['payout_address'],
                $data['payout_network'] ?? TradingSetting::get('live_account_crypto_network'), $data['note'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        $this->notifier->withdrawalRequested($req->load('user'));

        return redirect()->route('trade.live')
            ->with('success', 'Withdrawal request submitted. We\'ll send the USD to your wallet and notify you once it\'s processed.');
    }
}
