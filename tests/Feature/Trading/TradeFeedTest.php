<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeFeedTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create([
            'symbol' => 'SIMBTC',
            'asset_class' => 'sim',
            'supports_live' => false,
            'enabled' => true,
        ]);
        TradingSetting::set('default_start_balance', '10000');
        TradingSetting::set('live_mode_enabled', 'false');
        TradingSetting::set('default_mode', 'sim');
    }

    public function test_price_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('trade.price', [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
            ]));

        $response->assertOk()
            ->assertJsonStructure(['price', 'serverTime']);

        $this->assertIsFloat((float) $response->json('price'));
    }

    public function test_feed_endpoint_returns_candles(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('trade.feed', [
                'asset' => $this->asset->symbol,
                'mode' => 'sim',
                'interval' => '1m',
                'limit' => 10,
            ]));

        $response->assertOk()
            ->assertJsonStructure(['candles', 'price', 'mode']);

        $candles = $response->json('candles');
        $this->assertNotEmpty($candles);
        $this->assertArrayHasKey('time', $candles[0]);
        $this->assertArrayHasKey('open', $candles[0]);
        $this->assertArrayHasKey('high', $candles[0]);
        $this->assertArrayHasKey('low', $candles[0]);
        $this->assertArrayHasKey('close', $candles[0]);
    }

    public function test_price_endpoint_rejects_invalid_mode(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('trade.price', [
                'asset' => $this->asset->symbol,
                'mode' => 'unknown',
            ]))
            ->assertStatus(422);
    }

    public function test_price_endpoint_rejects_missing_asset(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('trade.price', ['mode' => 'sim']))
            ->assertStatus(422);
    }

    public function test_guest_cannot_access_feed(): void
    {
        $this->getJson(route('trade.feed', [
            'asset' => $this->asset->symbol,
            'mode' => 'sim',
        ]))->assertUnauthorized();
    }
}
