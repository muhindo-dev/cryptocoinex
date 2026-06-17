<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Services\Trading\LiveWalletService;
use App\Services\Trading\SettlementService;
use App\Services\Trading\TradeService;
use App\Services\Trading\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class TradeController extends Controller
{
    public function __construct(
        private readonly TradeService $tradeService,
        private readonly WalletService $walletService,
        private readonly SettlementService $settlementService,
        private readonly LiveWalletService $liveWalletService,
    ) {}

    /**
     * Current balance (and currency) for the requested account: demo → practice
     * wallet, live → real Live Account wallet.
     *
     * @return array{balance:int, currency:string, account:string}
     */
    private function accountBalance(string $account): array
    {
        if ($account === 'live') {
            $wallet = $this->liveWalletService->walletFor(Auth::id());

            return [
                'balance' => (int) $wallet->balance,
                'available' => $this->liveWalletService->availableBalance($wallet),
                'currency' => $wallet->currency,
                'account' => 'live',
            ];
        }

        return [
            'balance' => (int) ($this->walletService->walletFor(Auth::id())->balance),
            'currency' => 'USD',
            'account' => 'demo',
        ];
    }

    /**
     * GET /trade  — main trading screen.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = $this->walletService->walletFor($user->id);

        // Grant starting balance on first visit
        if (! $wallet->entries()->exists()) {
            $wallet = $this->walletService->grantStartingBalance($user->id);
        }

        $assets = Asset::where('enabled', true)->orderBy('name')->get();
        $selectedAsset = $assets->firstWhere('symbol', $request->get('asset')) ?? $assets->first();

        // Market-data mode is an admin/backend setting — students don't toggle it.
        $tradeMode = (TradingSetting::get('live_mode_enabled', 'false') === 'true'
            && TradingSetting::get('default_mode', 'sim') === 'live')
            ? 'live' : 'sim';

        // Live (real-money) account info for the Demo/Live switcher.
        $liveEnabled = TradingSetting::get('live_account_enabled', 'true') === 'true';
        $liveWallet = $this->liveWalletService->walletFor($user->id);
        $liveBalance = (int) $liveWallet->balance;
        $liveCurrency = $liveWallet->currency;
        $kycApproved = $user->isKycApproved();

        return view('trading.index', compact(
            'wallet', 'assets', 'selectedAsset', 'tradeMode',
            'liveEnabled', 'liveBalance', 'liveCurrency', 'kycApproved'
        ));
    }

    /**
     * POST /trade/place  — open a new trade.
     */
    public function place(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => ['required', 'string'],
            'mode' => ['required', 'in:sim,live'],
            'account' => ['nullable', 'in:demo,live'],
            'direction' => ['required', 'in:up,down'],
            'stake' => ['required', 'integer', 'min:1'],
        ]);

        $account = $validated['account'] ?? 'demo';
        $asset = Asset::where('symbol', $validated['asset'])->where('enabled', true)->firstOrFail();

        try {
            // No expiry — the position stays open until the trader closes it.
            $trade = $this->tradeService->open(
                Auth::user(),
                $asset,
                $validated['mode'],
                $validated['direction'],
                (int) $validated['stake'],
                null,
                $account,
            );

            return response()->json([
                'success' => true,
                'trade_id' => $trade->id,
                'account' => $trade->account,
                'direction' => $trade->direction,
                'stake' => (int) $trade->stake,
                'payout_percent' => (float) $trade->payout_percent,
                'entry_price' => (float) $trade->entry_price,
                'balance' => $this->accountBalance($account)['balance'],
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /trade/account?account=demo|live — balance for switching accounts.
     */
    public function accountState(Request $request): JsonResponse
    {
        $account = $request->get('account') === 'live' ? 'live' : 'demo';

        return response()->json($this->accountBalance($account) + [
            'live_enabled' => TradingSetting::get('live_account_enabled', 'true') === 'true',
        ]);
    }

    /**
     * POST /trade/{trade}/close — close an open position now at the live price.
     */
    public function close(Trade $trade): JsonResponse
    {
        abort_unless($trade->user_id === Auth::id(), 403);

        if ($trade->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'This trade is already closed.'], 422);
        }

        try {
            $trade = $this->settlementService->closeNow($trade);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'trade_id' => $trade->id,
            'account' => $trade->account,
            'status' => $trade->status,           // won | lost | tie
            'entry_price' => (float) $trade->entry_price,
            'exit_price' => (float) $trade->exit_price,
            'payout_amount' => (int) $trade->payout_amount,
            'stake' => (int) $trade->stake,
            'pnl' => (int) $trade->payout_amount - (int) $trade->stake,
            'balance' => $this->accountBalance($trade->account)['balance'],
        ]);
    }

    /**
     * GET /trade/{trade}  — poll settlement status.
     */
    public function show(Trade $trade): JsonResponse
    {
        abort_unless($trade->user_id === Auth::id(), 403);

        return response()->json([
            'id' => $trade->id,
            'status' => $trade->status,
            'exit_price' => $trade->exit_price ? (float) $trade->exit_price : null,
            'payout_amount' => $trade->payout_amount,
            'settled_at' => $trade->settled_at?->toISOString(),
            'balance' => Auth::user()->tradingWallet()->value('balance'),
        ]);
    }

    /**
     * GET /trade/open  — the user's currently open positions (for UI restore).
     */
    public function openPositions(Request $request): JsonResponse
    {
        $account = $request->get('account') === 'live' ? 'live' : 'demo';

        $trades = Auth::user()
            ->trades()
            ->with('asset')
            ->where('status', 'open')
            ->where('account', $account)
            ->latest('opened_at')
            ->get()
            ->map(fn (Trade $t) => [
                'id' => $t->id,
                'symbol' => $t->asset?->symbol,
                'direction' => $t->direction,
                'stake' => (int) $t->stake,
                'entry_price' => (float) $t->entry_price,
                'payout_percent' => (float) $t->payout_percent,
            ]);

        return response()->json([
            'account' => $account,
            'balance' => $this->accountBalance($account)['balance'],
            'positions' => $trades,
        ]);
    }

    /**
     * GET /trade/history/list  — paginated settled trades.
     */
    public function history(Request $request): JsonResponse
    {
        $account = $request->get('account') === 'live' ? 'live' : 'demo';

        $trades = Auth::user()
            ->trades()
            ->with('asset')
            ->where('account', $account)
            ->whereIn('status', ['won', 'lost', 'tie', 'void'])
            ->latest('settled_at')
            ->paginate(20);

        return response()->json($trades);
    }
}
