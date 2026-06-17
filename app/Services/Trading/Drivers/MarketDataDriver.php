<?php

namespace App\Services\Trading\Drivers;

use App\Models\Trading\Asset;

interface MarketDataDriver
{
    /**
     * Historical candles to seed the chart.
     * Each element: ['time' => unixSeconds, 'open' => float, 'high' => float, 'low' => float, 'close' => float]
     */
    public function candles(Asset $asset, string $interval, int $limit): array;

    /**
     * The current price right now — used for entry lock & settlement.
     */
    public function currentPrice(Asset $asset): float;

    /**
     * The latest (possibly still-forming) candle for live feed updates.
     */
    public function latestCandle(Asset $asset, string $interval): array;
}
