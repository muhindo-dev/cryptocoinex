<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Trading\AchievementService;
use Illuminate\Database\Seeder;

/**
 * Awards achievements to every student based on their seeded trade history.
 * Uses the real AchievementService so badges reflect genuine criteria.
 */
class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(AchievementService::class);

        $students = User::where('role', 'student')->get();
        $awarded = 0;

        foreach ($students as $student) {
            $awarded += count($service->evaluate($student));
        }

        $this->command->info("AchievementSeeder: awarded {$awarded} badges across {$students->count()} students.");
    }
}
