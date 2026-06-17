# Decisions Log — Trading Trainer

> Autonomous decisions made by the AI builder. Each entry: the question, the decision, and the reasoning.
> See `AGENT_BUILD_INSTRUCTIONS.md` §3a for the pre-authorised default table these draw from.

---

## DL1 — Legacy legal/LMS features: hide or delete?

**Decision:** **Hide** from navigation; leave tables, models, and routes dormant.

**Why:** Lowest-risk path. No data loss. The legal/LMS tables and models do not conflict with
the new `trading_*` namespace. Deletion, if ever desired, is a separate cleanup pass after the
trading trainer is proven stable. Aligns with ground rule #3 and roadmap recommendation (T1).

**How applied:** Remove (comment out) legal/LMS nav links from the admin sidebar in T1.
Do not touch routes, models, or migrations.

---

## DL2 — Starting practice balance

**Decision:** **10,000 PRACTICE$** (integer `10000` virtual units).

**Why:** Round number; feels meaningful enough for students to manage; matches the suggested
default in `AGENT_BUILD_INSTRUCTIONS.md` §3a. Stored in `trading_settings.default_start_balance`.
Label `PRACTICE$` is stored in `trading_wallets.currency_label`.

---

## DL3 — Cache backend

**Decision:** **Redis** (confirmed available: `redis-cli ping` → PONG).

**Why:** Redis is running on this machine. It is faster, supports TTL natively, and is the
right choice for the live-data cache warmer. Set `CACHE_DRIVER=redis` and
`QUEUE_CONNECTION=database` in `.env` (database queue for delayed settlement jobs — no extra
infra needed beyond the existing MySQL; Redis queue is an optional future upgrade).

**How applied:** Update `.env` in Phase 0 setup. Install `predis/predis` if not present.

---

## DL4 — First seeded assets

**Decision:** Seed three assets:
1. `BTCUSDT` — Bitcoin / USDT, crypto class, live-capable (Binance symbol `BTCUSDT`)
2. `ETHUSDT` — Ethereum / USDT, crypto class, live-capable (Binance symbol `ETHUSDT`)
3. `EURUSD-SIM` — Euro / USD (Simulated), sim class, sim-only

**Why:** Matches the suggested defaults. Two live-capable crypto assets cover Phase 3 (Binance
driver). One simulated forex asset demonstrates the sim-only path and gives instructors a
non-crypto teaching option.

---

## DL5 — Branding

**Decision:** **Keep** the `cryptocoinex` name and existing logos for now. Make `APP_NAME`
a `.env`/config value (it already is via `APP_NAME` in `.env`).

**Why:** The existing logos are already in place. Rebranding is cosmetic and deferrable.
The app name is trivially changeable via `APP_NAME` in `.env` if the operator wants to
rename it later.

---

## DL6 — Queue driver

**Decision:** Switch `QUEUE_CONNECTION` from `sync` to `database`.

**Why:** Settlement jobs (`SettleTradeJob`) are dispatched with a delay equal to the trade
expiry time. `sync` runs jobs immediately and synchronously, which would bypass the delay
and settle trades at placement rather than at expiry. The `database` queue driver requires
only an existing MySQL connection (already configured) and a `jobs` table migration.

**How applied:** Run `php artisan queue:table && php artisan migrate` during Phase 0 setup.
Update `QUEUE_CONNECTION=database` in `.env`. Document that `php artisan queue:work` must
run alongside the app.

---

## DL7 — PHP version note

**Observed:** Server is running PHP 8.4.7 (docs reference PHP 8.2). PHP 8.4 is fully
backward-compatible with 8.2 code. No action needed; the trading module will target 8.2
syntax (no 8.3/8.4-only features) for portability.

---

## DL8 — SimulatedDriver tick granularity

**Decision:** Two-level GBM: coarse hourly walk (COARSE_SECONDS=3600) + fine per-minute walk (FINE_SECONDS=60) within the current hour.

**Why:** The original per-second walk (TICK_SECONDS=1) loops from EPOCH
(2026-01-01) second-by-second. By 2026-06-10 that is ~13.8 M iterations per
`priceAt` call, making the feed endpoint take 17 s per request — far too slow
for AJAX polling or test suites. The two-level design reduces this to ~3,900
iterations (3,840 coarse + ≤60 fine), bringing response time below 300 ms.

**Trade-off:** Intra-hour prices are no longer globally consistent at the
per-second level (they are consistent within each hour boundary). For an
educational simulator this is acceptable; students see realistic-looking
candles and price movement without any real money at stake.

**How applied:** `streamPrices()` performs one forward pass collecting only the
requested tick indices. `priceAtWithCoarse()` does the fine walk anchored to the
hourly close using a per-hour sub-seed.

---

## DL9 — DeterministicRng: replace 64-bit LCG with 31-bit LCG

**Problem discovered during unit testing:** The original `DeterministicRng` used the standard
64-bit LCG multiplier `6364136223846793005`. PHP integers are *signed* 64-bit; when they
overflow, PHP silently converts to `float` (rather than wrapping as C does). Any state value
greater than ~1 caused `state * 6364136223846793005` to overflow to a float, which when cast
back to int via `& PHP_INT_MAX` collapsed to the same constant (PHP_INT_MIN + addend, masked).
This meant **every seed produced an identical RNG sequence**, making prices from different
assets equal. The bug was masked because the existing feature tests never compared prices
across assets or seeds directly.

**Fix:** Replace with a 31-bit LCG (Numerical Recipes constants: multiplier 1664525,
addend 1013904223). State is capped to `0x7FFFFFFF` after each step. The largest
intermediate value is `2147483647 * 1664525 ≈ 3.57e12`, well within int64 — no overflow
possible. Constructor mixes seed and startTick using multiplier 2654435761 (fits 32 bits),
so `startTick * 2654435761` is safe for ticks up to ~3.4 billion.

**Side effect:** All price paths changed from before the fix. Since no real data existed and
the old paths were statistically degenerate (no variance), this is strictly an improvement.

*Log started: 2026-06-10. All decisions above were made autonomously per ground rule #10.*
