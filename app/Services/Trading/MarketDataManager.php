<?php

namespace App\Services\Trading;

use App\Models\Trading\TradingSetting;
use App\Services\Trading\Drivers\BinanceLiveDriver;
use App\Services\Trading\Drivers\MarketDataDriver;
use App\Services\Trading\Drivers\SimulatedDriver;

class MarketDataManager
{
    /**
     * Return the correct driver for the given mode string ('sim' | 'live').
     *
     * If live mode is globally disabled or the live driver is unavailable,
     * falls back to SimulatedDriver transparently.
     */
    public static function for(string $mode): MarketDataDriver
    {
        if ($mode === 'live' && static::liveEnabled()) {
            return new BinanceLiveDriver;
        }

        return new SimulatedDriver;
    }

    private static function liveEnabled(): bool
    {
        return filter_var(
            TradingSetting::get('live_mode_enabled', 'true'),
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
