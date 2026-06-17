<?php

namespace Database\Seeders;

use App\Models\Trading\Trade;
use App\Models\User;
use App\Services\Trading\NotificationService;
use Illuminate\Database\Seeder;

/**
 * Generates trade-settled notifications for each student's most recent trades,
 * giving the in-app bell realistic history. (Achievement notifications are
 * created by AchievementSeeder via the AchievementService.)
 */
class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(NotificationService::class);
        $created = 0;

        foreach (User::where('role', 'student')->get() as $student) {
            $trades = Trade::where('user_id', $student->id)
                ->whereIn('status', ['won', 'lost', 'tie'])
                ->latest('settled_at')
                ->limit(rand(2, 4))
                ->get();

            foreach ($trades as $t) {
                $payout = (int) ($t->payout_amount ?? 0);
                $title = match ($t->status) {
                    'won' => "You won {$payout} USD! 🎉",
                    'lost' => 'Trade lost',
                    default => 'Trade tied — stake returned',
                };
                $n = $service->notify(
                    $student->id,
                    'trade_settled',
                    $title,
                    "#{$t->id} ".($t->asset->symbol ?? 'asset').' '.strtoupper($t->direction)." · stake {$t->stake} PRACTICE\$.",
                    null,
                    $t->status === 'won' ? 'fa-trophy' : ($t->status === 'lost' ? 'fa-circle-xmark' : 'fa-rotate-left'),
                    ['trade_id' => $t->id, 'status' => $t->status],
                );
                // Back-date + randomly mark some read
                $n->forceFill([
                    'created_at' => $t->settled_at,
                    'read_at' => rand(0, 1) ? $t->settled_at?->copy()->addMinutes(rand(1, 120)) : null,
                ])->save();
                $created++;
            }
        }

        $this->command->info("NotificationSeeder: created {$created} trade notifications.");
    }
}
