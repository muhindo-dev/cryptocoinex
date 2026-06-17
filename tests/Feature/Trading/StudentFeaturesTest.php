<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\NotificationService;
use App\Services\Trading\TradeService;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function asset(): Asset
    {
        return Asset::create([
            'symbol' => 'BTCUSDT', 'name' => 'Bitcoin', 'asset_class' => 'crypto',
            'payout_percent' => 80, 'min_stake' => 1, 'max_stake' => 10000,
            'allowed_expiries' => [30, 60, 300], 'supports_live' => true, 'live_symbol' => 'BTCUSDT',
            'sim_start_price' => 67000, 'sim_drift' => 0, 'sim_volatility' => 0.002, 'sim_seed' => 1, 'enabled' => true,
        ]);
    }

    private function student(): User
    {
        TradingSetting::set('default_start_balance', '10000');
        $u = User::create(['name' => 'Stud Ent', 'email' => 's@s.com', 'password' => bcrypt('password'), 'role' => 'student', 'kyc_status' => 'approved']);
        app(WalletService::class)->grantStartingBalance($u->id);

        return $u;
    }

    public function test_onboarding_creates_funded_student(): void
    {
        $this->asset();
        $this->get('/register')->assertStatus(200);
        $this->post('/register', [
            'name' => 'New One', 'email' => 'new@one.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('onboarding.profile'));

        $u = User::where('email', 'new@one.com')->first();
        $this->assertEquals('student', $u->role);
        $this->assertEquals(10000, $u->tradingWallet->balance);

        $this->actingAs($u)->post('/welcome', ['country' => 'Uganda', 'timezone' => 'Africa/Kampala', 'trading_experience' => 'beginner'])
            ->assertRedirect(route('trade.index', ['welcome' => 1]));
        $this->assertEquals('Uganda', $u->fresh()->country);
    }

    public function test_profile_page_and_update(): void
    {
        $u = $this->student();
        $this->actingAs($u)->get('/trade/profile')->assertStatus(200)->assertSee('Achievements');
        $this->actingAs($u)->post('/trade/profile', ['name' => 'Renamed', 'trading_experience' => 'advanced'])->assertRedirect();
        $this->assertEquals('Renamed', $u->fresh()->name);
    }

    public function test_history_page_and_csv_export(): void
    {
        $u = $this->student();
        $this->actingAs($u)->get('/trade/history')->assertStatus(200);
        $this->actingAs($u)->get('/trade/history/export')->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_leaderboard_renders_for_all_periods(): void
    {
        $u = $this->student();
        foreach (['weekly', 'monthly', 'all_time'] as $p) {
            $this->actingAs($u)->get("/trade/leaderboard?period={$p}")->assertStatus(200);
        }
    }

    public function test_notifications_feed_and_mark_read(): void
    {
        $u = $this->student();
        app(NotificationService::class)->notify($u->id, 'trade_settled', 'Hi', 'body');
        $this->actingAs($u)->get('/trade/notifications')->assertStatus(200)->assertJson(['unread' => 1]);
        $this->actingAs($u)->post('/trade/notifications/read')->assertJson(['marked' => 1]);
        $this->actingAs($u)->get('/trade/notifications')->assertJson(['unread' => 0]);
    }

    public function test_journal_note_save_and_authorization(): void
    {
        $u = $this->student();
        $asset = $this->asset();
        $trade = app(TradeService::class)->open($u, $asset, 'sim', 'up', 100, 60);
        $trade->update(['status' => 'won', 'settled_at' => now(), 'payout_amount' => 180]);

        $this->actingAs($u)->post("/trade/{$trade->id}/note", ['notes' => 'Good entry', 'tags' => ['breakout']])
            ->assertJson(['success' => true]);
        $this->assertEquals('Good entry', $trade->fresh()->notes);

        // Another user cannot annotate it
        $other = User::create(['name' => 'O', 'email' => 'o@o.com', 'password' => bcrypt('x'), 'role' => 'student', 'kyc_status' => 'approved']);
        $this->actingAs($other)->post("/trade/{$trade->id}/note", ['notes' => 'hack'])->assertStatus(403);

        $this->actingAs($u)->get('/trade/journal')->assertStatus(200)->assertSee('Good entry');
    }
}
