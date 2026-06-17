<?php

namespace Database\Factories\Trading;

use App\Models\Trading\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'symbol' => strtoupper(fake()->unique()->lexify('???USDT')),
            'name' => fake()->word().' Coin',
            'asset_class' => fake()->randomElement(['crypto', 'forex', 'sim']),
            'payout_percent' => 80,
            'min_stake' => 10,
            'max_stake' => 1000,
            'allowed_expiries' => [30, 60, 300],
            'supports_live' => false,
            'live_symbol' => null,
            'sim_start_price' => 30000.0,
            'sim_drift' => 0.0001,
            'sim_volatility' => 0.002,
            'sim_seed' => fake()->numberBetween(1000, 99999),
            'enabled' => true,
        ];
    }
}
