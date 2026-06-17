<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use App\Services\Trading\MarketDataManager;
use App\Services\Trading\TradeService;
use App\Services\Trading\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class TradingApiController extends Controller
{
    public function __construct(
        private readonly TradeService $tradeService,
        private readonly WalletService $walletService,
    ) {}

    /** GET /api/v1/assets */
    public function assets(): JsonResponse
    {
        $assets = Asset::where('enabled', true)->orderBy('display_order')->orderBy('name')
            ->get(['id', 'symbol', 'name', 'asset_class', 'category', 'payout_percent', 'min_stake', 'max_stake', 'allowed_expiries', 'supports_live', 'icon_url']);

        return response()->json(['data' => $assets]);
    }

    /** GET /api/v1/wallet */
    public function wallet(Request $request): JsonResponse
    {
        $wallet = $this->walletService->walletFor($request->user()->id);

        return response()->json([
            'balance' => (int) $wallet->balance,
            'peak_balance' => (int) $wallet->peak_balance,
            'currency' => $wallet->currency_label,
        ]);
    }

    /** GET /api/v1/price?asset=BTCUSDT&mode=sim */
    public function price(Request $request): JsonResponse
    {
        $data = $request->validate([
            'asset' => ['required', 'string'],
            'mode' => ['nullable', 'in:sim,live'],
        ]);
        $asset = Asset::where('symbol', $data['asset'])->where('enabled', true)->firstOrFail();
        $driver = MarketDataManager::for($data['mode'] ?? 'sim');

        return response()->json(['price' => $driver->currentPrice($asset)]);
    }

    /** POST /api/v1/trade */
    public function place(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => ['required', 'string'],
            'mode' => ['required', 'in:sim,live'],
            'direction' => ['required', 'in:up,down'],
            'stake' => ['required', 'integer', 'min:1'],
            'expiry_seconds' => ['required', 'integer', 'min:1'],
        ]);

        $asset = Asset::where('symbol', $validated['asset'])->where('enabled', true)->firstOrFail();

        try {
            $trade = $this->tradeService->open(
                $request->user(), $asset, $validated['mode'], $validated['direction'],
                (int) $validated['stake'], (int) $validated['expiry_seconds']
            );

            return response()->json([
                'success' => true,
                'trade' => $this->tradePayload($trade),
                'balance' => (int) $request->user()->tradingWallet()->value('balance'),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** GET /api/v1/trade/{trade} */
    public function show(Request $request, Trade $trade): JsonResponse
    {
        abort_unless($trade->user_id === $request->user()->id, 403);

        return response()->json(['trade' => $this->tradePayload($trade->load('asset'))]);
    }

    /** GET /api/v1/history */
    public function history(Request $request): JsonResponse
    {
        $trades = $request->user()->trades()
            ->with('asset')
            ->whereIn('status', ['won', 'lost', 'tie', 'void'])
            ->latest('settled_at')
            ->paginate(20);

        return response()->json($trades->through(fn (Trade $t) => $this->tradePayload($t)));
    }

    private function tradePayload(Trade $trade): array
    {
        return [
            'id' => $trade->id,
            'asset' => $trade->asset?->symbol,
            'direction' => $trade->direction,
            'mode' => $trade->mode,
            'stake' => (int) $trade->stake,
            'payout_percent' => (float) $trade->payout_percent,
            'entry_price' => (float) $trade->entry_price,
            'exit_price' => $trade->exit_price !== null ? (float) $trade->exit_price : null,
            'status' => $trade->status,
            'payout_amount' => $trade->payout_amount,
            'opened_at' => $trade->opened_at?->toISOString(),
            'expires_at' => $trade->expires_at?->toISOString(),
            'settled_at' => $trade->settled_at?->toISOString(),
        ];
    }
}
