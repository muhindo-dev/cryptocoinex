<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Asset;
use App\Services\Trading\MarketDataManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * GET /trade/feed?asset=BTCUSDT&interval=1m&mode=sim|live&limit=200
     *
     * Returns historical candles + current price to seed/update the chart.
     */
    public function feed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => ['required', 'string'],
            'interval' => ['nullable', 'string', 'in:1s,5s,10s,15s,25s,30s,1m,5m,15m,1h'],
            'mode' => ['nullable', 'string', 'in:sim,live'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $asset = Asset::where('symbol', $validated['asset'])
            ->where('enabled', true)
            ->firstOrFail();

        $interval = $validated['interval'] ?? '1m';
        $mode = $validated['mode'] ?? 'sim';
        $limit = (int) ($validated['limit'] ?? 200);

        if ($mode === 'live' && ! $asset->supports_live) {
            $mode = 'sim';
        }

        $driver = MarketDataManager::for($mode);
        $candles = $driver->candles($asset, $interval, $limit);
        $price = $driver->currentPrice($asset);

        return response()->json([
            'candles' => $candles,
            'price' => $price,
            'mode' => $mode,
            'asset' => $asset->symbol,
            'interval' => $interval,
            'serverTime' => time(),
        ]);
    }

    /**
     * GET /trade/price?asset=BTCUSDT&mode=sim|live
     *
     * Lightweight endpoint polled every ~1s for the current price + latest candle.
     */
    public function price(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => ['required', 'string'],
            'interval' => ['nullable', 'string', 'in:1s,5s,10s,15s,25s,30s,1m,5m,15m,1h'],
            'mode' => ['nullable', 'string', 'in:sim,live'],
        ]);

        $asset = Asset::where('symbol', $validated['asset'])
            ->where('enabled', true)
            ->firstOrFail();

        $interval = $validated['interval'] ?? '1m';
        $mode = $validated['mode'] ?? 'sim';

        if ($mode === 'live' && ! $asset->supports_live) {
            $mode = 'sim';
        }

        $driver = MarketDataManager::for($mode);

        return response()->json([
            'price' => $driver->currentPrice($asset),
            'candle' => $driver->latestCandle($asset, $interval),
            'mode' => $mode,
            'serverTime' => time(),
        ]);
    }
}
