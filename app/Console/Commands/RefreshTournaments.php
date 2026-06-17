<?php

namespace App\Console\Commands;

use App\Services\Trading\TournamentService;
use Illuminate\Console\Command;

/**
 * Syncs tournament statuses with the clock and finalizes any that have ended
 * (freezes standings, declares the winner, notifies participants).
 */
class RefreshTournaments extends Command
{
    protected $signature = 'trading:refresh-tournaments';

    protected $description = 'Activate/finalize tournaments based on their time window';

    public function handle(TournamentService $service): int
    {
        $finalized = $service->refreshStatuses();
        $this->info("Tournaments refreshed. Finalized: {$finalized}.");

        return self::SUCCESS;
    }
}
