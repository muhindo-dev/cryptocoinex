<?php

namespace App\Services\Trading\Drivers;

use App\Models\Trading\Asset;

/**
 * Deterministic GBM price simulator — two-level design for performance.
 *
 * Level 1 (coarse): 1-hour ticks from EPOCH to now — ~3,840 iterations for
 *   160 days. Gives the "macro" price path.
 *
 * Level 2 (fine): 1-minute ticks within any given hour, anchored to the
 *   hourly close. ≤60 iterations per call.
 *
 * Total for `currentPrice` or one candle: ≈3,900 iterations vs. the 13.8 M
 * that a per-second walk would need today.
 */
class SimulatedDriver implements MarketDataDriver
{
    /** Fixed simulation epoch — t=0 reference for all sim assets (2026-01-01 UTC). */
    private const EPOCH = 1735689600;

    private const COARSE_SECONDS = 3600; // 1 h per coarse tick

    private const FINE_SECONDS = 60;     // 1 min per fine tick

    private const SECONDS_PER_YEAR = 365.0 * 24.0 * 3600.0;

    // ── Public interface ─────────────────────────────────────────────────────

    public function currentPrice(Asset $asset): float
    {
        $now = time();

        return $this->priceAt($asset, $now);
    }

    public function candles(Asset $asset, string $interval, int $limit): array
    {
        $dtSec = $this->intervalToSeconds($interval);
        $now = time();

        $endBucket = intdiv($now, $dtSec);
        $startBucket = $endBucket - $limit;

        // Determine which coarse (hourly) ticks we need
        $startTs = $startBucket * $dtSec;
        $endTs = $endBucket * $dtSec;
        $coarseStart = intdiv($startTs, self::COARSE_SECONDS);
        $coarseEnd = intdiv($endTs, self::COARSE_SECONDS) + 1;

        $coarseTicks = range($coarseStart, $coarseEnd);
        $coarsePrices = $this->streamPrices($asset, $coarseTicks, self::COARSE_SECONDS);

        // Build candles
        $rng = new DeterministicRng((int) $asset->sim_seed + 7777, 0);
        $candles = [];

        for ($b = $startBucket; $b < $endBucket; $b++) {
            $bStart = $b * $dtSec;
            $bEnd = $bStart + $dtSec;

            // Real OHLC candles (TradingView-style): the body spans the bucket's
            // start price (open) to its end price (close) — consecutive buckets are
            // continuous because this candle's close == the next candle's open. We
            // also sample ~14 points across the bucket so high/low form genuine
            // wicks. The current (still-forming) candle closes at "now".
            $lastTs = min($bEnd, $now);
            $open = $this->priceAtWithCoarse($asset, $bStart, $coarsePrices);
            $close = $this->priceAtWithCoarse($asset, $lastTs, $coarsePrices);
            $high = max($open, $close);
            $low = min($open, $close);

            $step = max(1, intdiv($dtSec, 20));
            for ($ts = $bStart + $step; $ts < $lastTs; $ts += $step) {
                $p = $this->priceAtWithCoarse($asset, $ts, $coarsePrices);
                if ($p > $high) {
                    $high = $p;
                }
                if ($p < $low) {
                    $low = $p;
                }
            }

            // Extend the wicks slightly — but proportional to the candle's own range
            // (NOT a fixed % of the huge price), so bodies stay visible and candles
            // look like real TradingView candles.
            $bodyRange = max(abs($close - $open), ($high - $low));
            if ($bodyRange <= 0.0) {
                $bodyRange = $open * 0.0002; // perfectly flat candle fallback
            }
            $high += $bodyRange * abs($rng->nextFloat()) * 0.35;
            $low = max(0.0001, $low - $bodyRange * abs($rng->nextFloat()) * 0.35);

            $candles[] = [
                'time' => $bStart,
                'open' => round($open, 8),
                'high' => round($high, 8),
                'low' => round($low, 8),
                'close' => round($close, 8),
            ];
        }

        return $candles;
    }

    public function latestCandle(Asset $asset, string $interval): array
    {
        $candles = $this->candles($asset, $interval, 2);
        $last = end($candles);

        return $last ?: [
            'time' => time(),
            'open' => (float) $asset->sim_start_price,
            'high' => (float) $asset->sim_start_price,
            'low' => (float) $asset->sim_start_price,
            'close' => (float) $asset->sim_start_price,
        ];
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    /**
     * Price at arbitrary timestamp t using the two-level model.
     */
    private function priceAt(Asset $asset, int $t): float
    {
        $cTick = intdiv($t, self::COARSE_SECONDS);
        $coarsePrices = $this->streamPrices($asset, range(0, $cTick), self::COARSE_SECONDS);

        return $this->priceAtWithCoarse($asset, $t, $coarsePrices);
    }

    /**
     * Price at t given a pre-computed coarse price map (avoid re-walking).
     */
    private function priceAtWithCoarse(Asset $asset, int $t, array &$coarsePrices): float
    {
        $cTick = intdiv($t, self::COARSE_SECONDS);

        // Anchor = closest hourly price at or before t
        $anchorPrice = $coarsePrices[$cTick] ?? (float) $asset->sim_start_price;

        $offsetSeconds = $t - $cTick * self::COARSE_SECONDS;          // 0..3599
        $offsetMinutes = intdiv($offsetSeconds, self::FINE_SECONDS);  // whole minutes into the hour
        $secondsIntoMinute = $offsetSeconds - $offsetMinutes * self::FINE_SECONDS; // 0..59

        // At an exact minute boundary with no sub-second offset, the price is the
        // minute-level value — unchanged from the original two-level model. This
        // keeps minute/5m/1h candles (which sample on minute boundaries) identical.
        if ($offsetMinutes <= 0 && $secondsIntoMinute === 0) {
            return $anchorPrice;
        }

        $mu = (float) $asset->sim_drift;
        $sigma = (float) $asset->sim_volatility;
        $logS = log($anchorPrice);

        // ── Level 2: minute walk from the hourly anchor ──
        if ($offsetMinutes > 0) {
            $fineSeed = (int) $asset->sim_seed ^ ($cTick * 2654435761);
            $dt = self::FINE_SECONDS / self::SECONDS_PER_YEAR;
            $drift = ($mu - 0.5 * $sigma * $sigma) * $dt;
            $vol = $sigma * sqrt($dt);

            $rng = new DeterministicRng($fineSeed, $cTick);
            for ($i = 0; $i < $offsetMinutes; $i++) {
                $z = $rng->nextNormal();
                $logS += $drift + $vol * $z;
            }
        }

        // ── Level 3: per-second walk within the current minute ──
        // Gives sub-minute candles (10s/25s/30s) genuine intra-candle movement.
        // Deterministic and anchored per-minute, so it never alters minute-boundary
        // prices used by entries, settlements and minute+ candles.
        if ($secondsIntoMinute > 0) {
            $minuteIndex = $cTick * 60 + $offsetMinutes;
            $subSeed = (int) $asset->sim_seed ^ ($minuteIndex * 40503);
            $dt2 = 1.0 / self::SECONDS_PER_YEAR;
            $drift2 = ($mu - 0.5 * $sigma * $sigma) * $dt2;
            $vol2 = $sigma * sqrt($dt2);

            $rng2 = new DeterministicRng($subSeed, $minuteIndex + 1);
            for ($s = 0; $s < $secondsIntoMinute; $s++) {
                $z = $rng2->nextNormal();
                $logS += $drift2 + $vol2 * $z;
            }
        }

        return max(0.0001, exp($logS));
    }

    /**
     * Single streaming walk at given tick resolution.
     * Returns [tickIndex => price] for all ticks in $tickRange.
     *
     * Only the ticks in $tickRange are returned, but the RNG is consumed
     * for every tick from 0 to max($tickRange) — ensuring path consistency.
     */
    private function streamPrices(Asset $asset, array $tickRange, int $tickSeconds): array
    {
        if (empty($tickRange)) {
            return [];
        }

        $s0 = (float) $asset->sim_start_price;
        $mu = (float) $asset->sim_drift;
        $sigma = (float) $asset->sim_volatility;
        $seed = (int) $asset->sim_seed;

        $dt = $tickSeconds / self::SECONDS_PER_YEAR;
        $drift = ($mu - 0.5 * $sigma * $sigma) * $dt;
        $vol = $sigma * sqrt($dt);

        $epochTick = intdiv(self::EPOCH, $tickSeconds);
        // Use epochTick (small integer, ~482136) not EPOCH (1.7B) to avoid int overflow in RNG init.
        $rng = new DeterministicRng($seed, $epochTick);
        $logS = log($s0);

        $sortedTicks = $tickRange;
        sort($sortedTicks);
        $maxTick = max($sortedTicks);

        $tickSet = array_flip($sortedTicks); // O(1) membership test
        $result = [];

        for ($tick = $epochTick; $tick <= $maxTick; $tick++) {
            $z = $rng->nextNormal();
            $logS += $drift + $vol * $z;

            if (isset($tickSet[$tick])) {
                $result[$tick] = max(0.0001, exp($logS));
            }
        }

        return $result;
    }

    private function intervalToSeconds(string $interval): int
    {
        return match ($interval) {
            '1s' => 1,
            '5s' => 5,
            '10s' => 10,
            '15s' => 15,
            '25s' => 25,
            '30s' => 30,
            '1m' => 60,
            '5m' => 300,
            '15m' => 900,
            '1h' => 3600,
            default => 60,
        };
    }
}

/**
 * Deterministic pseudo-random number generator — 31-bit LCG.
 *
 * Uses Numerical Recipes constants (multiplier=1664525, addend=1013904223) and
 * a 31-bit state so that every arithmetic operation stays within PHP's signed
 * 64-bit integer range and never overflows to float. The original 64-bit LCG
 * constants (6364136223846793005) overflow on every step in PHP, causing all
 * seeds to collapse to the same state (DL9).
 */
class DeterministicRng
{
    private int $state;

    public function __construct(int $seed, int $startTick)
    {
        // $startTick * 2654435761 stays within int64 for ticks up to ~3.4 billion.
        $mixed = ($startTick * 2654435761 + 1013904223) & PHP_INT_MAX;
        $this->state = ($seed ^ $mixed) & 0x7FFFFFFF;
        if ($this->state === 0) {
            $this->state = 1;
        }
    }

    public function nextFloat(): float
    {
        // LCG: state * 1664525 fits in int64 for any 31-bit state value.
        $this->state = (($this->state * 1664525) + 1013904223) & 0x7FFFFFFF;

        return $this->state / 2147483647.0;
    }

    public function nextNormal(): float
    {
        $u1 = max(1e-10, $this->nextFloat());
        $u2 = $this->nextFloat();

        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
    }
}
