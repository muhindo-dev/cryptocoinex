<?php

namespace App\Jobs\Trading;

use App\Models\Trading\Trade;
use App\Services\Trading\MarketDataManager;
use App\Services\Trading\SettlementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SettleTradeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        private readonly int $tradeId
    ) {}

    public function handle(): void
    {
        $trade = Trade::find($this->tradeId);

        if (! $trade || $trade->status !== 'open') {
            return;
        }

        $driver = MarketDataManager::for($trade->mode);
        $exitPrice = $driver->currentPrice($trade->asset);

        // Resolve from the container so the live-wallet dependency is injected.
        app(SettlementService::class)->settle($trade, $exitPrice);
    }

    public function failed(Throwable $e): void
    {
        // Log but do not rethrow — a failed settlement should not crash the worker.
        logger()->error("SettleTradeJob failed for trade #{$this->tradeId}: {$e->getMessage()}");
    }
}
