<?php

namespace Database\Seeders;

use App\Services\Trading\LeaderboardService;
use Illuminate\Database\Seeder;

/**
 * Persists leaderboard snapshots for each period from current trade data.
 */
class LeaderboardSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(LeaderboardService::class);
        $total = 0;

        foreach (LeaderboardService::PERIODS as $period) {
            $total += $service->snapshot($period);
        }

        $this->command->info("LeaderboardSeeder: wrote {$total} snapshot rows.");
    }
}
