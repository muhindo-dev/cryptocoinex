<?php

namespace Database\Seeders;

use App\Models\Trading\Asset;
use App\Models\Trading\Tournament;
use App\Models\Trading\TournamentParticipant;
use App\Models\User;
use App\Services\Trading\TournamentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds a mix of past (finalized), active, and upcoming tournaments with
 * participants drawn from the demo students.
 */
class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $btc = Asset::where('symbol', 'BTCUSDT')->first();
        $students = User::where('role', 'student')->inRandomOrder()->limit(30)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students — run CryptocoineuxSeeder first.');

            return;
        }

        $service = app(TournamentService::class);

        $defs = [
            ['1-Hour BTC Sprint', $btc?->id, 5000, Carbon::now()->subDays(5), Carbon::now()->subDays(5)->addHour(), 'past'],
            ['Weekend Warrior', null, 10000, Carbon::now()->subDays(3), Carbon::now()->subDays(2), 'past'],
            ['Live Now: Crypto Cup', $btc?->id, 5000, Carbon::now()->subMinutes(20), Carbon::now()->addMinutes(40), 'active'],
            ['Friday Night Showdown', null, 7500, Carbon::now()->addDays(2), Carbon::now()->addDays(2)->addHours(2), 'upcoming'],
        ];

        foreach ($defs as [$name, $assetId, $start, $startsAt, $endsAt, $phase]) {
            $t = Tournament::create([
                'name' => $name,
                'description' => 'Compete for the top spot. Highest balance wins!',
                'asset_id' => $assetId,
                'starting_balance' => $start,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $phase === 'upcoming' ? 'upcoming' : ($phase === 'active' ? 'active' : 'ended'),
            ]);

            foreach ($students->random(rand(8, 20)) as $s) {
                TournamentParticipant::firstOrCreate(
                    ['tournament_id' => $t->id, 'user_id' => $s->id],
                    ['joined_at' => $startsAt]
                );
            }

            if ($phase === 'past') {
                $service->finalize($t);
            }
        }

        $this->command->info('TournamentSeeder: created '.count($defs).' tournaments.');
    }
}
