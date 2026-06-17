<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAssetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_index_lists_assets(): void
    {
        Asset::factory()->create(['symbol' => 'BTCUSDT', 'name' => 'Bitcoin', 'asset_class' => 'crypto']);

        $response = $this->actingAs($this->admin)->get(route('admin.trading.assets.index'));

        $response->assertOk()->assertSee('BTCUSDT');
    }

    public function test_create_form_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.trading.assets.create'))
            ->assertOk();
    }

    public function test_store_creates_asset(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.trading.assets.store'), $this->validPayload())
            ->assertRedirect(route('admin.trading.assets.index'));

        $this->assertDatabaseHas('trading_assets', ['symbol' => 'TESTUSDT']);
    }

    public function test_store_rejects_missing_symbol(): void
    {
        $payload = $this->validPayload();
        unset($payload['symbol']);

        $this->actingAs($this->admin)
            ->post(route('admin.trading.assets.store'), $payload)
            ->assertSessionHasErrors('symbol');
    }

    public function test_edit_form_renders(): void
    {
        $asset = Asset::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.trading.assets.edit', $asset))
            ->assertOk();
    }

    public function test_update_modifies_asset(): void
    {
        $asset = Asset::factory()->create(['payout_percent' => 80]);

        $payload = $this->validPayload(['payout_percent' => 85]);

        $this->actingAs($this->admin)
            ->put(route('admin.trading.assets.update', $asset), $payload)
            ->assertRedirect(route('admin.trading.assets.index'));

        $this->assertDatabaseHas('trading_assets', ['id' => $asset->id, 'payout_percent' => 85]);
    }

    public function test_destroy_deletes_asset(): void
    {
        $asset = Asset::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.trading.assets.destroy', $asset))
            ->assertRedirect(route('admin.trading.assets.index'));

        $this->assertDatabaseMissing('trading_assets', ['id' => $asset->id]);
    }

    public function test_guest_cannot_access(): void
    {
        $this->get(route('admin.trading.assets.index'))->assertRedirect(route('admin.login'));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'symbol' => 'TESTUSDT',
            'name' => 'Test Coin',
            'asset_class' => 'crypto',
            'payout_percent' => 80,
            'min_stake' => 10,
            'max_stake' => 1000,
            'allowed_expiries' => '30,60,300',
            'supports_live' => 0,
            'live_symbol' => '',
            'sim_start_price' => 1000,
            'sim_drift' => 0.0001,
            'sim_volatility' => 0.002,
            'sim_seed' => 42,
            'enabled' => 1,
        ], $overrides);
    }
}
