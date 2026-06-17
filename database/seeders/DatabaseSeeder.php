<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,           // roles + permissions matrix
            TradingSeeder::class,        // assets + global settings (idempotent)
            AdminUserSeeder::class,      // admin user
            CryptocoineuxSeeder::class,  // 50 students + wallets + 200+ trades
            AchievementSeeder::class,    // badges from trade history (+ notifications)
            NotificationSeeder::class,   // trade-settled notifications
            LeaderboardSeeder::class,    // weekly/monthly/all-time snapshots
            TournamentSeeder::class,     // past/active/upcoming tournaments
            EducationSeeder::class,      // 6 tracks, 42 lessons with YouTube videos
        ]);
    }
}
