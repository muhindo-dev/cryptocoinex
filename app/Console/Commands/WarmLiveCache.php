<?php

namespace App\Console\Commands;

use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use App\Services\Trading\Drivers\BinanceLiveDriver;
use Illuminate\Console\Command;

/**
 * Polls Binance REST every 2 seconds for all live-capable enabled assets
 * and writes the results into Redis cache.
 *
 * The student-facing /trade/feed and /trade/price endpoints read only
 * from cache — never call Binance directly. Run this in the background:
 *
 *   php artisan trading:warm-cache
 */
class WarmLiveCache extends Command
{
    protected $signature = 'trading:warm-cache
        {--interval=2 : Poll interval in seconds (loop mode)}
        {--once : Run a single warming pass and exit (used by the scheduler)}';

    protected $description = 'Keep Binance live data cache warm for all live-capable assets';

    public function handle(): int
    {
        $liveEnabled = filter_var(
            TradingSetting::get('live_mode_enabled', 'true'),
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $liveEnabled) {
            $this->info('Live mode is disabled globally. Nothing to warm.');

            return 0;
        }

        $assets = Asset::where('enabled', true)->where('supports_live', true)->get();

        if ($assets->isEmpty()) {
            $this->info('No live-capable assets found.');

            return 0;
        }

        $driver = new BinanceLiveDriver;

        // ── Single-pass mode (scheduler) ──
        if ($this->option('once')) {
            $this->warmAll($driver, $assets);

            return 0;
        }

        // ── Continuous loop mode (manual / long-running worker) ──
        $interval = (int) $this->option('interval');
        $this->info("Warming cache for {$assets->count()} asset(s) every {$interval}s. Press Ctrl+C to stop.");

        while (true) {
            $this->warmAll($driver, $assets);
            sleep($interval);
        }

        return 0;
    }

    /**
     * Warm price + latest candle for every given asset, once.
     */
    private function warmAll(BinanceLiveDriver $driver, $assets): void
    {
        foreach ($assets as $asset) {
            try {
                $driver->currentPrice($asset);
                $driver->latestCandle($asset, '1m');
                $this->line('['.now()->format('H:i:s')."] {$asset->symbol} cached.");
            } catch (\Throwable $e) {
                $this->warn("{$asset->symbol} error: {$e->getMessage()}");
            }
        }
    }
}
