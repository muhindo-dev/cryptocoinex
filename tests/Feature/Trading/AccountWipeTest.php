<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Achievement;
use App\Models\Trading\Asset;
use App\Models\Trading\LeaderboardSnapshot;
use App\Models\Trading\Tournament;
use App\Models\Trading\TournamentParticipant;
use App\Models\Trading\TradingNotification;
use App\Models\Trading\TradingSetting;
use App\Models\Trading\Wallet;
use App\Models\Trading\WalletEntry;
use App\Models\User;
use App\Services\Trading\TradeService;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountWipeTest extends TestCase
{
    use RefreshDatabase;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        TradingSetting::set('default_start_balance', '10000');
        TradingSetting::set('allow_student_reset', 'true');
        TradingSetting::set('live_account_enabled', 'true');
        $this->asset = Asset::create([
            'symbol' => 'BTCUSDT', 'name' => 'Bitcoin', 'asset_class' => 'crypto',
            'payout_percent' => 80, 'min_stake' => 1, 'max_stake' => 10000,
            'allowed_expiries' => [30, 60, 300], 'supports_live' => true, 'live_symbol' => 'BTCUSDT',
            'sim_start_price' => 67000, 'sim_drift' => 0, 'sim_volatility' => 0.002, 'sim_seed' => 1, 'enabled' => true,
        ]);
    }

    /** Build a student with the full spread of money/activity records. */
    private function loadedStudent(): User
    {
        $u = User::create(['name' => 'Whale', 'email' => 'w@w.com', 'password' => bcrypt('x'), 'role' => 'student', 'kyc_status' => 'approved']);
        app(WalletService::class)->grantStartingBalance($u->id);
        app(TradeService::class)->open($u, $this->asset, 'sim', 'up', 100, 60); // trade + ledger entries
        Achievement::create(['user_id' => $u->id, 'type' => 'first_trade', 'title' => 'First', 'achieved_at' => now(), 'created_at' => now()]);
        TradingNotification::create(['user_id' => $u->id, 'type' => 'trade_settled', 'title' => 'x', 'created_at' => now()]);
        LeaderboardSnapshot::create(['user_id' => $u->id, 'period' => 'all_time', 'period_date' => now()->toDateString(), 'rank' => 1, 'computed_at' => now()]);
        $t = Tournament::create(['name' => 'Cup', 'starting_balance' => 5000, 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour(), 'status' => 'active']);
        TournamentParticipant::create(['tournament_id' => $t->id, 'user_id' => $u->id, 'joined_at' => now()]);

        return $u;
    }

    private function assertWipedClean(User $u): void
    {
        $this->assertSame(0, $u->trades()->count(), 'trades remain');
        $this->assertSame(0, Achievement::where('user_id', $u->id)->count(), 'achievements remain');
        $this->assertSame(0, TradingNotification::where('user_id', $u->id)->count(), 'notifications remain');
        $this->assertSame(0, LeaderboardSnapshot::where('user_id', $u->id)->count(), 'leaderboard remains');
        $this->assertSame(0, TournamentParticipant::where('user_id', $u->id)->count(), 'tournament entries remain');

        // Fresh wallet exists with exactly the starting balance and a single topup entry.
        $wallet = Wallet::where('user_id', $u->id)->first();
        $this->assertNotNull($wallet);
        $this->assertSame(10000, (int) $wallet->balance);
        $this->assertSame(1, WalletEntry::where('wallet_id', $wallet->id)->count());

        // User + login survive.
        $this->assertDatabaseHas('users', ['id' => $u->id]);
    }

    public function test_service_wipe_deletes_everything_and_refunds(): void
    {
        $u = $this->loadedStudent();
        $this->assertGreaterThan(0, $u->trades()->count());

        $result = app(WalletService::class)->wipeAndReset($u);

        $this->assertGreaterThanOrEqual(1, $result['deleted']['trades']);
        $this->assertWipedClean($u);
    }

    public function test_wipe_never_touches_live_trades_or_the_live_wallet(): void
    {
        $u = $this->loadedStudent();
        $live = app(\App\Services\Trading\LiveWalletService::class);
        $liveWallet = $live->walletFor($u->id);
        $u->forceFill(['kyc_status' => 'approved'])->save();
        $live->credit($liveWallet, 1000, 'deposit', 'seed');

        // An open real-money live trade.
        $liveTrade = app(TradeService::class)->open($u, $this->asset, 'sim', 'up', 200, null, 'live');
        $this->assertSame(800, $live->walletFor($u->id)->balance);

        app(WalletService::class)->wipeAndReset($u);

        // Live trade and live wallet are completely untouched.
        $this->assertDatabaseHas('trading_trades', ['id' => $liveTrade->id, 'status' => 'open', 'account' => 'live']);
        $this->assertSame(800, $live->walletFor($u->id)->balance);
        $this->assertSame(0, $u->fresh()->trades()->where('account', 'demo')->count());
    }

    public function test_student_can_wipe_own_account(): void
    {
        $u = $this->loadedStudent();
        $this->actingAs($u)->post('/trade/wallet/wipe')->assertRedirect();
        $this->assertWipedClean($u);
    }

    public function test_student_wipe_blocked_when_disabled(): void
    {
        TradingSetting::set('allow_student_reset', 'false');
        $u = $this->loadedStudent();
        $this->actingAs($u)->post('/trade/wallet/wipe')->assertStatus(403);
        $this->assertGreaterThan(0, $u->trades()->count()); // nothing deleted
    }

    public function test_admin_can_wipe_student(): void
    {
        $u = $this->loadedStudent();
        $admin = User::create(['name' => 'A', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'role' => 'admin', 'is_admin' => true]);

        $this->actingAs($admin)->post("/admin/trading/students/{$u->id}/wipe")->assertRedirect();
        $this->assertWipedClean($u);
    }

    public function test_only_target_user_is_wiped(): void
    {
        $a = $this->loadedStudent();
        $b = User::create(['name' => 'B', 'email' => 'b@b.com', 'password' => bcrypt('x'), 'role' => 'student', 'kyc_status' => 'approved']);
        app(WalletService::class)->grantStartingBalance($b->id);
        app(TradeService::class)->open($b, $this->asset, 'sim', 'up', 50, 60);

        app(WalletService::class)->wipeAndReset($a);

        $this->assertGreaterThan(0, $b->trades()->count(), 'other user must be untouched');
    }
}
