<?php

namespace Database\Seeders;

use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use Illuminate\Database\Seeder;

class TradingSeeder extends Seeder
{
    public function run(): void
    {
        // ── Global settings ──
        $settings = [
            'default_start_balance' => '10000',
            'default_mode' => 'sim',
            'live_mode_enabled' => 'true',
            'tie_policy' => 'refund',
            'allow_student_reset' => 'true',

            // ── Live Account (real money, USD / crypto) defaults ──
            'live_account_enabled' => 'true',
            'live_account_currency' => 'USD',
            'live_account_min_deposit' => '10',        // USD
            'live_account_min_withdrawal' => '20',     // USD
            'live_account_crypto_address' => '',       // admin sets the receiving USDT address
            'live_account_crypto_network' => 'USDT (TRC20)',
            'live_account_payment_link' => '',         // optional hosted checkout / pay link
            'live_account_payment_instructions' => "Fund your account with crypto (USDT). Send the exact USD amount, then upload a screenshot of your payment for verification.\n\n1. Send USDT to the wallet address shown on the deposit screen (or use the payment link).\n2. Take a screenshot of the completed transaction.\n3. Tap \"New deposit\", enter the USD amount and upload your screenshot.\n4. Our team verifies the payment and credits your Live Account — usually within a few hours. You'll get an email the moment it's approved.\n\nOnly send the network shown (e.g. USDT TRC20). Sending the wrong asset or network may result in loss of funds.",
        ];

        foreach ($settings as $key => $value) {
            // Don't overwrite an admin-edited value on re-seed; only seed if absent.
            if (TradingSetting::get($key) === null) {
                TradingSetting::set($key, $value);
            }
        }

        // ── Demo assets ──
        // [symbol, name, class, payout, min, max, expiries, live, liveSymbol,
        //  startPrice, drift, vol, seed, category, difficulty, featured, order]
        $defs = [
            ['BTCUSDT', 'Bitcoin / USDT', 'crypto', 80, 1, 10000, [30, 60, 300], true, 'BTCUSDT', 67000, 0, 0.00200, 100001, 'crypto', 'beginner', true, 1],
            ['ETHUSDT', 'Ethereum / USDT', 'crypto', 80, 1, 10000, [30, 60, 300], true, 'ETHUSDT', 3500, 0, 0.00250, 100002, 'crypto', 'beginner', true, 2],
            ['BNBUSDT', 'Binance Coin / USDT', 'crypto', 78, 1, 10000, [30, 60, 300], true, 'BNBUSDT', 600, 0, 0.00280, 100003, 'crypto', 'intermediate', false, 3],
            ['SOLUSDT', 'Solana / USDT', 'crypto', 78, 1, 10000, [30, 60, 300], true, 'SOLUSDT', 150, 0, 0.00350, 100004, 'crypto', 'intermediate', false, 4],
            ['EURUSD-SIM', 'Euro / USD (Simulated)', 'forex', 75, 1, 5000, [30, 60, 300], false, null, 1.08500, 0, 0.00050, 200001, 'forex', 'beginner', false, 5],
            ['GBPUSD-SIM', 'Pound / USD (Simulated)', 'forex', 75, 1, 5000, [30, 60, 300], false, null, 1.27000, 0, 0.00055, 200002, 'forex', 'beginner', false, 6],
            ['USDJPY-SIM', 'USD / Yen (Simulated)', 'forex', 75, 1, 5000, [30, 60, 300], false, null, 157.50000, 0, 0.00050, 200003, 'forex', 'intermediate', false, 7],
            ['XAUUSD-SIM', 'Gold / USD (Simulated)', 'sim', 77, 1, 8000, [60, 300, 900], false, null, 2350.00, 0.00002, 0.00080, 300001, 'commodity', 'beginner', true, 8],
            ['XAGUSD-SIM', 'Silver / USD (Simulated)', 'sim', 76, 1, 8000, [60, 300, 900], false, null, 30.50, 0.00001, 0.00120, 300002, 'commodity', 'intermediate', false, 9],
            ['OIL-SIM', 'Crude Oil / USD (Simulated)', 'sim', 74, 1, 8000, [60, 300, 900], false, null, 78.40, 0, 0.00150, 300003, 'commodity', 'advanced', false, 10],
            ['SPXUSD-SIM', 'S&P 500 (Simulated)', 'sim', 76, 1, 10000, [60, 300, 900], false, null, 5450.00, 0.00003, 0.00060, 400001, 'index', 'beginner', false, 11],
            ['AAPL-SIM', 'Apple Inc (Simulated)', 'stock', 75, 1, 8000, [60, 300, 900], false, null, 213.00, 0.00002, 0.00120, 400002, 'stock', 'intermediate', false, 12],
            ['TSLA-SIM', 'Tesla Inc (Simulated)', 'stock', 73, 1, 8000, [60, 300, 900], false, null, 245.00, 0, 0.00300, 400003, 'stock', 'advanced', false, 13],
        ];

        foreach ($defs as $d) {
            Asset::updateOrCreate(['symbol' => $d[0]], [
                'name' => $d[1], 'asset_class' => $d[2], 'payout_percent' => $d[3],
                'min_stake' => $d[4], 'max_stake' => $d[5], 'allowed_expiries' => $d[6],
                'supports_live' => $d[7], 'live_symbol' => $d[8],
                'sim_start_price' => $d[9], 'sim_drift' => $d[10], 'sim_volatility' => $d[11], 'sim_seed' => $d[12],
                'category' => $d[13], 'difficulty' => $d[14], 'is_featured' => $d[15], 'display_order' => $d[16],
                'enabled' => true,
            ]);
        }
    }
}
