<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\Tournament;
use App\Models\User;
use App\Services\Trading\TournamentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    private function tournament(array $overrides = []): Tournament
    {
        return Tournament::create(array_merge([
            'name' => 'Test Cup', 'starting_balance' => 5000,
            'starts_at' => now()->subMinutes(10), 'ends_at' => now()->addMinutes(50), 'status' => 'active',
        ], $overrides));
    }

    public function test_student_can_view_and_join(): void
    {
        $u = User::create(['name' => 'A', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'role' => 'student']);
        $t = $this->tournament();

        $this->actingAs($u)->get('/trade/tournaments')->assertStatus(200)->assertSee('Test Cup');
        $this->actingAs($u)->get("/trade/tournaments/{$t->id}")->assertStatus(200);
        $this->actingAs($u)->post("/trade/tournaments/{$t->id}/join")->assertRedirect();
        $this->assertDatabaseHas('trading_tournament_participants', ['tournament_id' => $t->id, 'user_id' => $u->id]);
    }

    public function test_cannot_join_ended_tournament(): void
    {
        $u = User::create(['name' => 'B', 'email' => 'b@b.com', 'password' => bcrypt('x'), 'role' => 'student']);
        $t = $this->tournament(['status' => 'ended', 'starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()]);

        $this->actingAs($u)->post("/trade/tournaments/{$t->id}/join")->assertRedirect();
        $this->assertDatabaseMissing('trading_tournament_participants', ['tournament_id' => $t->id, 'user_id' => $u->id]);
    }

    public function test_finalize_declares_winner(): void
    {
        $asset = Asset::create([
            'symbol' => 'BTCUSDT', 'name' => 'Bitcoin', 'asset_class' => 'crypto',
            'payout_percent' => 80, 'min_stake' => 1, 'max_stake' => 10000,
            'allowed_expiries' => [30], 'supports_live' => true, 'live_symbol' => 'BTCUSDT',
            'sim_start_price' => 67000, 'sim_drift' => 0, 'sim_volatility' => 0.002, 'sim_seed' => 1, 'enabled' => true,
        ]);
        $t = $this->tournament(['asset_id' => $asset->id]);
        $winner = User::create(['name' => 'W', 'email' => 'w@w.com', 'password' => bcrypt('x'), 'role' => 'student']);
        $loser = User::create(['name' => 'L', 'email' => 'l@l.com', 'password' => bcrypt('x'), 'role' => 'student']);

        $service = app(TournamentService::class);
        $service->join($t, $winner);
        $service->join($t, $loser);

        // Winner has a winning trade inside the window; loser has a loss.
        $this->trade($winner, $asset, 'won', 100, 180);
        $this->trade($loser, $asset, 'lost', 100, null);

        $service->finalize($t->fresh());

        $this->assertEquals($winner->id, $t->fresh()->winner_user_id);
        $this->assertDatabaseHas('trading_tournament_participants', [
            'tournament_id' => $t->id, 'user_id' => $winner->id, 'final_rank' => 1,
        ]);
    }

    private function trade(User $u, Asset $asset, string $status, int $stake, ?int $payout): void
    {
        \App\Models\Trading\Trade::create([
            'user_id' => $u->id, 'asset_id' => $asset->id, 'mode' => 'sim', 'direction' => 'up',
            'stake' => $stake, 'payout_percent' => 80, 'entry_price' => 67000, 'exit_price' => 67100,
            'opened_at' => now()->subMinutes(2), 'expires_at' => now()->subMinute(), 'settled_at' => now(),
            'expiry_seconds' => 30, 'status' => $status, 'payout_amount' => $payout,
        ]);
    }
}
