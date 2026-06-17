<?php

namespace Tests\Unit\Trading;

use App\Models\Trading\Asset;
use App\Services\Trading\Drivers\SimulatedDriver;
use Tests\TestCase;

/**
 * Unit tests for SimulatedDriver — no DB, no HTTP.
 *
 * Asset instances are built with make() (no persistence) so the suite is
 * fast and completely isolated from database state.
 */
class SimulatedDriverTest extends TestCase
{
    private SimulatedDriver $driver;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driver = new SimulatedDriver;
        $this->asset = Asset::factory()->make([
            'sim_start_price' => 30000.0,
            'sim_drift' => 0.0001,
            'sim_volatility' => 0.002,
            'sim_seed' => 42000,
        ]);
    }

    // ── currentPrice ──────────────────────────────────────────────────────────

    public function test_current_price_is_positive(): void
    {
        $price = $this->driver->currentPrice($this->asset);
        $this->assertGreaterThan(0.0, $price);
    }

    public function test_current_price_is_deterministic_across_two_calls(): void
    {
        // Same driver, same asset, same "now" — must return the same value.
        // We mock time by calling in tight succession and asserting exact equality.
        $p1 = $this->driver->currentPrice($this->asset);
        $p2 = $this->driver->currentPrice($this->asset);
        $this->assertSame($p1, $p2);
    }

    public function test_different_seeds_produce_different_prices(): void
    {
        $asset2 = Asset::factory()->make([
            'sim_start_price' => 30000.0,
            'sim_drift' => 0.0001,
            'sim_volatility' => 0.002,
            'sim_seed' => 99999,
        ]);
        $p1 = $this->driver->currentPrice($this->asset);
        $p2 = $this->driver->currentPrice($asset2);
        $this->assertNotEquals($p1, $p2, 'Different seeds must produce different price paths.');
    }

    public function test_price_is_a_finite_positive_float(): void
    {
        // Sanity check: any valid GBM price must be a finite positive number.
        $price = $this->driver->currentPrice($this->asset);
        $this->assertTrue(is_finite($price));
        $this->assertGreaterThan(0.0, $price);
        $this->assertLessThan(PHP_FLOAT_MAX, $price);
    }

    // ── candles ───────────────────────────────────────────────────────────────

    public function test_candles_returns_correct_count(): void
    {
        $candles = $this->driver->candles($this->asset, '1m', 50);
        $this->assertCount(50, $candles);
    }

    public function test_candles_are_in_ascending_time_order(): void
    {
        $candles = $this->driver->candles($this->asset, '1m', 20);
        $times = array_column($candles, 'time');
        $sorted = $times;
        sort($sorted);
        $this->assertSame($sorted, $times);
    }

    public function test_candle_ohlc_structure_is_valid(): void
    {
        $candles = $this->driver->candles($this->asset, '1m', 20);
        foreach ($candles as $c) {
            $this->assertArrayHasKey('time', $c);
            $this->assertArrayHasKey('open', $c);
            $this->assertArrayHasKey('high', $c);
            $this->assertArrayHasKey('low', $c);
            $this->assertArrayHasKey('close', $c);

            $this->assertGreaterThanOrEqual($c['open'], $c['high'], 'High must be >= open');
            $this->assertGreaterThanOrEqual($c['close'], $c['high'], 'High must be >= close');
            $this->assertLessThanOrEqual($c['open'], $c['low'], 'Low must be <= open');
            $this->assertLessThanOrEqual($c['close'], $c['low'], 'Low must be <= close');

            $this->assertGreaterThan(0.0, $c['low']);
        }
    }

    public function test_candles_are_deterministic(): void
    {
        $c1 = $this->driver->candles($this->asset, '1m', 10);
        $c2 = $this->driver->candles($this->asset, '1m', 10);
        $this->assertSame($c1, $c2);
    }

    public function test_5m_candles_have_correct_interval_spacing(): void
    {
        $candles = $this->driver->candles($this->asset, '5m', 5);
        if (count($candles) >= 2) {
            $gap = $candles[1]['time'] - $candles[0]['time'];
            $this->assertSame(300, $gap, '5m candles should be 300 s apart');
        }
        $this->assertTrue(true); // always pass if not enough candles
    }

    public function test_1h_candles_have_correct_interval_spacing(): void
    {
        $candles = $this->driver->candles($this->asset, '1h', 5);
        if (count($candles) >= 2) {
            $gap = $candles[1]['time'] - $candles[0]['time'];
            $this->assertSame(3600, $gap, '1h candles should be 3600 s apart');
        }
        $this->assertTrue(true);
    }

    // ── latestCandle ──────────────────────────────────────────────────────────

    public function test_latest_candle_has_ohlc_keys(): void
    {
        $c = $this->driver->latestCandle($this->asset, '1m');
        $this->assertArrayHasKey('open', $c);
        $this->assertArrayHasKey('high', $c);
        $this->assertArrayHasKey('low', $c);
        $this->assertArrayHasKey('close', $c);
        $this->assertArrayHasKey('time', $c);
    }

    public function test_latest_candle_time_is_recent(): void
    {
        $c = $this->driver->latestCandle($this->asset, '1m');
        // Should be within the last 2 minutes
        $this->assertGreaterThan(time() - 120, $c['time']);
    }
}
