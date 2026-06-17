<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\AchievementService;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementServiceTest extends TestCase
{
    use RefreshDatabase;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        TradingSetting::set('default_start_balance', '10000');
        $this->asset = Asset::create([
            'symbol' => 'BTCUSDT', 'name' => 'Bitcoin', 'asset_class' => 'crypto',
            'payout_percent' => 80, 'min_stake' => 1, 'max_stake' => 10000,
            'allowed_expiries' => [30], 'supports_live' => true, 'live_symbol' => 'BTCUSDT',
            'sim_start_price' => 67000, 'sim_drift' => 0, 'sim_volatility' => 0.002, 'sim_seed' => 1, 'enabled' => true,
        ]);
    }

    private function settledTrade(User $u, string $status, int $stake = 100, ?int $payout = null): Trade
    {
        return Trade::create([
            'user_id' => $u->id, 'asset_id' => $this->asset->id, 'mode' => 'sim', 'direction' => 'up',
            'stake' => $stake, 'payout_percent' => 80, 'entry_price' => 67000,
            'exit_price' => 67100, 'opened_at' => now()->subMinutes(5), 'expires_at' => now()->subMinutes(4),
            'settled_at' => now(), 'expiry_seconds' => 30, 'status' => $status,
            'payout_amount' => $payout ?? ($status === 'won' ? $stake + (int) ($stake * 0.8) : null),
        ]);
    }

    public function test_first_trade_badge_awarded(): void
    {
        $u = User::create(['name' => 'A', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'role' => 'student']);
        app(WalletService::class)->grantStartingBalance($u->id);
        $this->settledTrade($u, 'won');

        $awarded = app(AchievementService::class)->evaluate($u);

        $this->assertContains('first_trade', $u->achievements()->pluck('type')->all());
        $this->assertNotEmpty($awarded);
        // Earning a badge creates an achievement_earned notification
        $this->assertDatabaseHas('trading_notifications', ['user_id' => $u->id, 'type' => 'achievement_earned']);
    }

    public function test_win_streak_3_badge(): void
    {
        $u = User::create(['name' => 'B', 'email' => 'b@b.com', 'password' => bcrypt('x'), 'role' => 'student']);
        app(WalletService::class)->grantStartingBalance($u->id);
        foreach (range(1, 3) as $i) {
            $t = $this->settledTrade($u, 'won');
            $t->update(['settled_at' => now()->addSeconds($i)]); // strictly increasing
        }

        app(AchievementService::class)->evaluate($u);

        $this->assertContains('win_streak_3', $u->achievements()->pluck('type')->all());
    }

    public function test_badge_not_double_awarded(): void
    {
        $u = User::create(['name' => 'C', 'email' => 'c@c.com', 'password' => bcrypt('x'), 'role' => 'student']);
        app(WalletService::class)->grantStartingBalance($u->id);
        $this->settledTrade($u, 'won');

        $service = app(AchievementService::class);
        $service->evaluate($u);
        $second = $service->evaluate($u);

        $this->assertEmpty($second); // nothing new the second time
        $this->assertEquals(1, $u->achievements()->where('type', 'first_trade')->count());
    }
}
