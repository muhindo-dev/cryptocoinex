<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TradeLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create([
            'symbol' => 'SIMBTC',
            'asset_class' => 'sim',
            'supports_live' => false,
            'enabled' => true,
            'min_stake' => 10,
            'max_stake' => 1000,
            'payout_percent' => 80,
            'allowed_expiries' => [30, 60, 300],
        ]);

        TradingSetting::set('default_start_balance', '10000');
        TradingSetting::set('live_mode_enabled', 'false');
        TradingSetting::set('default_mode', 'sim');
        TradingSetting::set('tie_policy', 'refund');

        app(WalletService::class)->grantStartingBalance($this->user->id);
    }

    public function test_trade_index_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('trade.index'))
            ->assertOk()
            ->assertSee('Practice Balance');
    }

    public function test_place_trade_succeeds(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('trade.place'), [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'direction' => 'up',
                'stake' => 100,
                'expiry_seconds' => 60,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['trade_id', 'entry_price', 'direction', 'stake', 'balance']);

        $this->assertDatabaseHas('trading_trades', [
            'user_id' => $this->user->id,
            'direction' => 'up',
            'stake' => 100,
            'status' => 'open',
        ]);
    }

    public function test_place_trade_debits_wallet(): void
    {
        $wallet = app(WalletService::class)->walletFor($this->user->id);
        $this->assertEquals(10000, $wallet->balance);

        $this->actingAs($this->user)
            ->postJson(route('trade.place'), [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'direction' => 'down',
                'stake' => 200,
                'expiry_seconds' => 30,
            ])
            ->assertOk();

        $this->assertEquals(9800, $wallet->fresh()->balance);
    }

    public function test_place_trade_rejects_overdraft(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('trade.place'), [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'direction' => 'up',
                'stake' => 99999,
                'expiry_seconds' => 60,
            ])
            ->assertStatus(422);
    }

    public function test_place_trade_rejects_invalid_direction(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('trade.place'), [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'direction' => 'sideways',
                'stake' => 100,
                'expiry_seconds' => 60,
            ])
            ->assertStatus(422);
    }

    public function test_open_position_has_no_countdown_and_can_be_closed(): void
    {
        // Open a position — no expiry sent; it gets a long backstop, not a countdown.
        $place = $this->actingAs($this->user)
            ->postJson(route('trade.place'), [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'direction' => 'up',
                'stake' => 100,
            ])->assertOk();

        $tradeId = $place->json('trade_id');
        $trade = \App\Models\Trading\Trade::find($tradeId);
        $this->assertSame('open', $trade->status);
        $this->assertTrue($trade->expires_at->isAfter(now()->addDay())); // far backstop, not a short timer

        // Close it now at the live price.
        $close = $this->actingAs($this->user)
            ->postJson(route('trade.close', $trade))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['status', 'exit_price', 'payout_amount', 'pnl', 'balance']);

        $this->assertContains($close->json('status'), ['won', 'lost', 'tie']);
        $this->assertNotSame('open', $trade->fresh()->status);
    }

    public function test_cannot_close_someone_elses_trade(): void
    {
        $other = \App\Models\User::factory()->create(['role' => 'student']);
        app(WalletService::class)->grantStartingBalance($other->id);
        $trade = app(\App\Services\Trading\TradeService::class)
            ->open($other, $this->asset, 'sim', 'up', 100);

        $this->actingAs($this->user)
            ->postJson(route('trade.close', $trade))
            ->assertStatus(403);
    }

    public function test_cannot_close_an_already_closed_trade(): void
    {
        $trade = app(\App\Services\Trading\TradeService::class)
            ->open($this->user, $this->asset, 'sim', 'up', 100);
        app(\App\Services\Trading\SettlementService::class)->closeNow($trade);

        $this->actingAs($this->user)
            ->postJson(route('trade.close', $trade->fresh()))
            ->assertStatus(422);
    }

    public function test_trade_show_returns_trade_data(): void
    {
        $placedResponse = $this->actingAs($this->user)
            ->postJson(route('trade.place'), [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'direction' => 'up',
                'stake' => 50,
                'expiry_seconds' => 60,
            ]);

        $tradeId = $placedResponse->json('trade_id');

        $this->actingAs($this->user)
            ->getJson(route('trade.show', ['trade' => $tradeId]))
            ->assertOk()
            ->assertJsonPath('id', $tradeId)
            ->assertJsonPath('status', 'open');
    }

    public function test_history_returns_paginated_trades(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('trade.history'))
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    public function test_wallet_show_returns_balance(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('trade.wallet'))
            ->assertOk()
            ->assertJsonPath('balance', 10000);
    }

    public function test_wallet_ledger_returns_entries(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('trade.wallet.ledger'))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_guest_cannot_place_trade(): void
    {
        $this->postJson(route('trade.place'), [
            'asset' => $this->asset->symbol,
            'mode' => 'sim',
            'direction' => 'up',
            'stake' => 100,
            'expiry_seconds' => 60,
        ])->assertUnauthorized();
    }
}
