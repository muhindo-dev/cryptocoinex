<?php

namespace Tests\Feature\Api;

use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
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

    public function test_register_returns_token_and_funds_wallet(): void
    {
        TradingSetting::set('default_start_balance', '10000');
        $res = $this->postJson('/api/v1/register', [
            'name' => 'Api User', 'email' => 'api@user.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);
        $res->assertStatus(201)->assertJsonStructure(['token', 'user' => ['id', 'balance']]);
        $this->assertEquals(10000, $res->json('user.balance'));
    }

    public function test_login_and_protected_access(): void
    {
        $u = User::create(['name' => 'A', 'email' => 'a@a.com', 'password' => bcrypt('password123'), 'role' => 'student', 'kyc_status' => 'approved']);
        $this->postJson('/api/v1/login', ['email' => 'a@a.com', 'password' => 'wrong'])->assertStatus(422);
        // No token => unauthorized (checked first, before a header is set)
        $this->getJson('/api/v1/me')->assertStatus(401);

        $token = $this->postJson('/api/v1/login', ['email' => 'a@a.com', 'password' => 'password123'])
            ->assertStatus(200)->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')->assertStatus(200)->assertJsonPath('user.email', 'a@a.com');
    }

    public function test_place_trade_and_history_via_api(): void
    {
        Queue::fake(); // don't run the settlement job synchronously
        TradingSetting::set('default_start_balance', '10000');
        $this->asset();
        $u = User::create(['name' => 'A', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'role' => 'student', 'kyc_status' => 'approved']);
        app(WalletService::class)->grantStartingBalance($u->id);
        Sanctum::actingAs($u);

        $this->getJson('/api/v1/assets')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/wallet')->assertStatus(200)->assertJsonPath('balance', 10000);

        $place = $this->postJson('/api/v1/trade', [
            'asset' => 'BTCUSDT', 'mode' => 'sim', 'direction' => 'up', 'stake' => 100, 'expiry_seconds' => 60,
        ]);
        $place->assertStatus(201)->assertJsonPath('success', true);
        $this->assertEquals(9900, $place->json('balance'));

        $id = $place->json('trade.id');
        $this->getJson("/api/v1/trade/{$id}")->assertStatus(200)->assertJsonPath('trade.id', $id);
        $this->getJson('/api/v1/history')->assertStatus(200)->assertJsonStructure(['data', 'current_page']);
    }
}
