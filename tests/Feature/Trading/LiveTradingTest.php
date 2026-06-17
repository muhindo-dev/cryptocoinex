<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\LiveWallet;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\LiveWalletService;
use App\Services\Trading\SettlementService;
use App\Services\Trading\TradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Real-money (live-account) trading must never touch the practice wallet, must
 * settle against the LiveWallet, and must keep that ledger consistent.
 */
class LiveTradingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Asset $asset;

    private LiveWalletService $live;

    private TradeService $trades;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->user = User::factory()->create(['role' => 'student', 'kyc_status' => 'approved']);
        $this->asset = Asset::factory()->create([
            'symbol' => 'SIMBTC', 'asset_class' => 'sim', 'supports_live' => false,
            'enabled' => true, 'min_stake' => 1, 'max_stake' => 100000, 'payout_percent' => 80,
            'allowed_expiries' => [30, 60, 300],
        ]);

        TradingSetting::set('live_account_enabled', 'true');
        TradingSetting::set('live_account_currency', 'USD');
        TradingSetting::set('tie_policy', 'refund');

        $this->live = app(LiveWalletService::class);
        $this->trades = app(TradeService::class);

        // Fund the live wallet with 1,000 real units.
        $this->live->credit($this->live->walletFor($this->user->id), 1000, 'deposit', 'seed');
    }

    public function test_opening_a_live_trade_debits_the_live_wallet_not_practice(): void
    {
        $practiceBefore = app(\App\Services\Trading\WalletService::class)->walletFor($this->user->id)->balance;

        $trade = $this->trades->open($this->user, $this->asset, 'sim', 'up', 200, null, 'live');

        $this->assertSame('live', $trade->account);
        $this->assertSame(800, $this->live->walletFor($this->user->id)->balance); // 1000 - 200
        $this->assertSame($practiceBefore, app(\App\Services\Trading\WalletService::class)->walletFor($this->user->id)->balance);
        $this->assertTrue($this->live->verifyLedger($this->live->walletFor($this->user->id)));
    }

    public function test_live_trade_cannot_exceed_live_balance(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->trades->open($this->user, $this->asset, 'sim', 'up', 5000, null, 'live');
    }

    public function test_live_trade_is_blocked_when_live_account_disabled(): void
    {
        TradingSetting::set('live_account_enabled', 'false');
        $this->expectException(\RuntimeException::class);
        $this->trades->open($this->user, $this->asset, 'sim', 'up', 100, null, 'live');
    }

    public function test_winning_live_trade_credits_live_wallet_with_payout(): void
    {
        $trade = $this->trades->open($this->user, $this->asset, 'sim', 'up', 200, null, 'live');
        // Settle as a win: exit above entry for an 'up' trade.
        app(SettlementService::class)->settle($trade, (float) $trade->entry_price + 10);

        // 1000 - 200 stake + (200 + 80% of 200 = 360) = 1160
        $wallet = $this->live->walletFor($this->user->id);
        $this->assertSame(1160, $wallet->balance);
        $this->assertSame('won', $trade->fresh()->status);
        $this->assertTrue($this->live->verifyLedger($wallet));
    }

    public function test_losing_live_trade_keeps_the_debited_stake(): void
    {
        $trade = $this->trades->open($this->user, $this->asset, 'sim', 'up', 200, null, 'live');
        app(SettlementService::class)->settle($trade, (float) $trade->entry_price - 10); // up + price down = loss

        $this->assertSame(800, $this->live->walletFor($this->user->id)->balance); // stake stays gone
        $this->assertSame('lost', $trade->fresh()->status);
    }

    public function test_live_trade_does_not_award_achievements_or_leaderboard(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\Trading\TradeSettled::class]);
        $trade = $this->trades->open($this->user, $this->asset, 'sim', 'up', 200, null, 'live');
        app(SettlementService::class)->settle($trade, (float) $trade->entry_price + 10);

        \Illuminate\Support\Facades\Event::assertNotDispatched(\App\Events\Trading\TradeSettled::class);
    }

    public function test_demo_trade_still_uses_practice_wallet_and_fires_events(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\Trading\TradeSettled::class]);
        app(\App\Services\Trading\WalletService::class)->grantStartingBalance($this->user->id);

        $trade = $this->trades->open($this->user, $this->asset, 'sim', 'up', 100); // default demo
        $this->assertSame('demo', $trade->account);
        $this->assertSame(1000, $this->live->walletFor($this->user->id)->balance); // live untouched

        app(SettlementService::class)->settle($trade, (float) $trade->entry_price + 10);
        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\Trading\TradeSettled::class);
    }

    public function test_place_and_close_live_trade_via_http(): void
    {
        $place = $this->actingAs($this->user)->postJson(route('trade.place'), [
            'asset' => $this->asset->symbol, 'mode' => 'sim', 'account' => 'live',
            'direction' => 'up', 'stake' => 150,
        ])->assertOk()->assertJsonPath('account', 'live')->assertJsonPath('balance', 850);

        $trade = Trade::find($place->json('trade_id'));
        $this->assertSame('live', $trade->account);

        $this->actingAs($this->user)->postJson(route('trade.close', $trade))
            ->assertOk()->assertJsonPath('account', 'live');

        $this->assertContains($trade->fresh()->status, ['won', 'lost', 'tie']);
    }

    public function test_account_state_endpoint_returns_live_balance(): void
    {
        $this->actingAs($this->user)->getJson(route('trade.account', ['account' => 'live']))
            ->assertOk()
            ->assertJsonPath('account', 'live')
            ->assertJsonPath('balance', 1000)
            ->assertJsonPath('currency', 'USD');
    }

    public function test_open_positions_are_filtered_by_account(): void
    {
        app(\App\Services\Trading\WalletService::class)->grantStartingBalance($this->user->id);
        $this->trades->open($this->user, $this->asset, 'sim', 'up', 100);            // demo
        $this->trades->open($this->user, $this->asset, 'sim', 'down', 100, null, 'live'); // live

        $this->actingAs($this->user)->getJson(route('trade.openlist', ['account' => 'live']))
            ->assertOk()->assertJsonCount(1, 'positions')->assertJsonPath('positions.0.direction', 'down');

        $this->actingAs($this->user)->getJson(route('trade.openlist', ['account' => 'demo']))
            ->assertOk()->assertJsonCount(1, 'positions')->assertJsonPath('positions.0.direction', 'up');
    }
}
