<?php

namespace App\Services\Trading\Drivers;

use App\Models\Trading\Asset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Serves real Binance market data.
 *
 * Fetches from Binance REST once and caches for a few seconds so all
 * concurrent student requests share a single upstream hit.
 * Never called directly from a student request path — feed endpoints
 * read only from cache; the cache is populated by the CacheWarmer command.
 *
 * Falls back to SimulatedDriver when upstream is unavailable.
 */
class BinanceLiveDriver implements MarketDataDriver
{
    private const BASE = 'https://api.binance.com/api/v3';

    private const CACHE_TTL = 5; // seconds

    /** Kline intervals Binance actually supports (sub-minute is limited to 1s). */
    private const BINANCE_INTERVALS = ['1s', '1m', '3m', '5m', '15m', '30m', '1h', '2h', '4h', '1d'];

    public function candles(Asset $asset, string $interval, int $limit): array
    {
        $symbol = strtoupper($asset->live_symbol ?? $asset->symbol);

        // Binance has no 10s/25s/30s klines — serve those from the simulator so the
        // chart still moves at the requested granularity in live mode.
        if (! in_array($interval, self::BINANCE_INTERVALS, true)) {
            return $this->simFallback($symbol)->candles($this->dummyAsset($symbol), $interval, $limit);
        }

        $cacheKey = "binance_candles_{$symbol}_{$interval}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($symbol, $interval, $limit) {
            try {
                $response = Http::timeout(5)->get(self::BASE.'/klines', [
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'limit' => $limit,
                ]);

                if (! $response->ok()) {
                    return $this->simFallback($symbol)->candles($this->dummyAsset($symbol), $interval, $limit);
                }

                return array_map(fn ($k) => [
                    'time' => (int) ($k[0] / 1000),
                    'open' => (float) $k[1],
                    'high' => (float) $k[2],
                    'low' => (float) $k[3],
                    'close' => (float) $k[4],
                ], $response->json());
            } catch (\Throwable) {
                return $this->simFallback($symbol)->candles($this->dummyAsset($symbol), $interval, $limit);
            }
        });
    }

    public function currentPrice(Asset $asset): float
    {
        $symbol = strtoupper($asset->live_symbol ?? $asset->symbol);
        $cacheKey = "binance_price_{$symbol}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($symbol, $asset) {
            try {
                $response = Http::timeout(3)->get(self::BASE.'/ticker/price', ['symbol' => $symbol]);

                if ($response->ok()) {
                    return (float) $response->json('price');
                }
            } catch (\Throwable) {
            }

            return $this->simFallback($symbol)->currentPrice($asset);
        });
    }

    public function latestCandle(Asset $asset, string $interval): array
    {
        $symbol = strtoupper($asset->live_symbol ?? $asset->symbol);

        if (! in_array($interval, self::BINANCE_INTERVALS, true)) {
            return $this->simFallback($symbol)->latestCandle($this->dummyAsset($symbol), $interval);
        }

        $cacheKey = "binance_latest_{$symbol}_{$interval}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($symbol, $asset, $interval) {
            try {
                $response = Http::timeout(3)->get(self::BASE.'/klines', [
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'limit' => 1,
                ]);

                if ($response->ok()) {
                    $k = $response->json()[0] ?? null;
                    if ($k) {
                        return [
                            'time' => (int) ($k[0] / 1000),
                            'open' => (float) $k[1],
                            'high' => (float) $k[2],
                            'low' => (float) $k[3],
                            'close' => (float) $k[4],
                        ];
                    }
                }
            } catch (\Throwable) {
            }

            return $this->simFallback($symbol)->latestCandle($asset, $interval);
        });
    }

    private function simFallback(string $symbol): SimulatedDriver
    {
        return new SimulatedDriver;
    }

    /** Minimal Asset-like object for fallback calls. */
    private function dummyAsset(string $symbol): Asset
    {
        $asset = new Asset;
        $asset->symbol = $symbol;
        $asset->sim_start_price = 50000;
        $asset->sim_drift = 0;
        $asset->sim_volatility = 0.002;
        $asset->sim_seed = crc32($symbol);

        return $asset;
    }
}
