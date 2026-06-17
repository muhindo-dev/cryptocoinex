# Architecture & Engineering Guidelines

> System design for the Trading Trainer module, plus the conventions every contributor
> follows. Read `01_DECISIONS.md` first.

---

## 1. High-level architecture

```
┌──────────────────────────────────────────────────────────────────────┐
│                          BROWSER (student)                            │
│   Blade page + jQuery + AJAX + TradingView Lightweight Charts          │
│   - renders candlestick chart                                          │
│   - polls /trade/feed every ~1s for new candles/price                 │
│   - posts /trade/place, polls /trade/{id} for settlement              │
└───────────────▲───────────────────────────────┬──────────────────────┘
                │ JSON over AJAX                 │ JSON over AJAX
┌───────────────┴───────────────────────────────▼──────────────────────┐
│                       LARAVEL APP (backend)                           │
│                                                                        │
│   Controllers (Trade)                                                 │
│     FeedController      -> returns candles/price for an asset+mode    │
│     TradeController     -> place trade, get trade status              │
│     WalletController    -> balance, ledger                            │
│                                                                        │
│   Services (business logic)                                           │
│     MarketDataManager   -> picks a driver by mode (sim | live)        │
│     TradeService        -> validates + opens trades                  │
│     SettlementService   -> settles a trade at expiry                 │
│     WalletService       -> ledger debit/credit, balance              │
│                                                                        │
│   MarketData drivers (the SEAM — see §3)                              │
│     SimulatedDriver     -> GBM/random-walk price generator           │
│     BinanceLiveDriver   -> REST seed + cached live ticks             │
│                                                                        │
│   Queue jobs                                                          │
│     SettleTradeJob       -> dispatched with delay = expiry            │
│                                                                        │
│   Eloquent models + MySQL  (see 05_DATA_MODEL.md)                     │
│   Existing admin shell (reused) for management screens               │
└──────────────────────────────────────────────────────────────────────┘
                                │
                ┌───────────────▼────────────────┐
                │  Binance public API (live mode) │
                │  REST klines + WS streams       │
                └─────────────────────────────────┘
```

---

## 2. Layering rules

1. **Controllers are thin.** Validate input, call a service, return JSON. No business logic.
2. **Services own business logic.** Trades, settlement, wallet math, and data selection
   live in services — testable in isolation.
3. **Drivers own data acquisition.** Nothing outside the MarketData drivers knows whether
   data is simulated or live.
4. **Models own persistence + relationships only.** No external calls in models.
5. **Jobs own deferred work.** Settlement happens in a queued job, never inline in a request.

---

## 3. The data-source seam (most important design decision)

The toggle between **simulated** and **live** must never leak into the rest of the system.
We enforce this with a single interface that both drivers implement:

```php
interface MarketDataDriver
{
    /** Historical candles to seed the chart. */
    public function candles(Asset $asset, string $interval, int $limit): array;

    /** The current price right now (used for entry lock & settlement). */
    public function currentPrice(Asset $asset): float;

    /** The latest (possibly forming) candle, for live updates. */
    public function latestCandle(Asset $asset, string $interval): array;
}
```

- `SimulatedDriver` generates prices from a seeded random walk (see `04_DATA_AND_ENGINE.md`).
- `BinanceLiveDriver` fetches/caches real Binance data.

`MarketDataManager::for($mode)` returns the right driver. **Everything else — chart feed,
entry price, settlement — calls the interface and is identical for both modes.** This is
what makes the toggle trivial and what lets us add a WebSocket driver later without touching
the frontend.

### Frontend contract is mode-agnostic

The browser calls the **same** endpoints regardless of mode; mode is just a parameter:

```
GET /trade/feed?asset=BTCUSDT&interval=1m&mode=sim   -> { candles:[...], price: 67000.12 }
GET /trade/feed?asset=BTCUSDT&interval=1m&mode=live  -> { candles:[...], price: 67012.40 }
```

---

## 4. Request flows

### 4a. Live chart (polling)

```
Browser every ~1s:  GET /trade/feed?asset=&interval=&mode=
Backend:            MarketDataManager::for(mode)->latestCandle(...) + currentPrice(...)
Browser:            chart.update(candle); update price label
```

### 4b. Placing a trade

```
Browser:   POST /trade/place { asset, mode, direction:UP|DOWN, stake, expirySeconds }
Backend:   TradeService::open()
             - validate stake within asset limits & wallet balance
             - lock entryPrice = driver->currentPrice(asset)
             - record openedAt, expiresAt = now + expirySeconds
             - WalletService::debit(stake)  // stake held
             - persist Trade(status=OPEN)
             - dispatch SettleTradeJob delayed until expiresAt
Backend ->  { tradeId, entryPrice, expiresAt }
```

### 4c. Settlement

```
SettleTradeJob runs at expiresAt:
   SettlementService::settle(trade)
     - exitPrice = driver->currentPrice(asset)   // same mode the trade was opened in
     - win = (direction==UP && exit>entry) || (direction==DOWN && exit<entry)
     - exit==entry -> TIE policy (configurable: refund stake)
     - if win: WalletService::credit(stake + stake*payout%)
     - mark Trade(status=WON|LOST|TIE, exitPrice, settledAt)
Browser:  was polling GET /trade/{id} -> sees status change -> animates result
```

> **Mode is stored on the trade.** A trade opened in live mode settles against live price;
> a sim trade settles against sim price. The driver used at settlement = the trade's mode.

---

## 5. Routing & namespacing conventions

- Student trading routes under prefix `/trade`, name prefix `trade.`, `auth` middleware.
- Admin trading screens under the **existing** `/admin` group, reusing `admin` middleware
  and the existing admin layout. New admin controllers: `Admin\Trading\*`.
- New models under `App\Models\Trading\` (e.g. `Asset`, `Trade`, `Wallet`, `WalletEntry`)
  to keep the trading domain separate from legacy legal/LMS models.
- New services under `App\Services\Trading\`. Drivers under `App\Services\Trading\Drivers\`.
- New jobs under `App\Jobs\Trading\`.

---

## 6. Coding guidelines

- **PSR-12** + Laravel Pint (already in dev deps). Run `./vendor/bin/pint` before commits.
- **Money is integer cents/units**, never floats, in the wallet ledger. Prices may be
  float/decimal but **amounts of virtual currency are integers** to avoid rounding drift.
- **Server is the single source of truth.** The client never computes balances or outcomes;
  it only displays what the server returns.
- **Idempotent settlement.** `SettleTradeJob` must no-op if the trade is already settled
  (guard on `status == OPEN`) so retries are safe.
- **Validate every input** with Form Requests; never trust stake, expiry, or asset from the
  client without checking against admin-configured limits.
- **Cache live data** (Binance) for a few seconds to respect rate limits and decouple user
  polling from upstream calls (see `04_DATA_AND_ENGINE.md`).
- **Feature-flag live mode** so it can be disabled globally if an upstream feed misbehaves;
  the app then falls back to simulated.
- **Keep frontend JS modular**: one `trade.js` module exposing `initChart()`, `startFeed()`,
  `placeTrade()`, `pollTrade()`. Avoid inline script soup.

---

## 7. Security & integrity guidelines

- Auth-gate all trading and wallet endpoints; a user can only touch **their own** wallet/trades.
- Rate-limit `POST /trade/place` (Laravel throttle) to stop spam/abuse.
- All wallet mutations go through `WalletService` inside DB transactions; never update a
  balance column directly.
- Never expose admin-only fields (e.g. simulation seed) to the student client.
- CSRF protection on all POST routes (Laravel default — keep it).

---

## 8. Performance guidelines

- Polling endpoints must be cheap: serve candles from cache, not a fresh DB/Binance hit per request.
- Index `trades(user_id, status)` and `wallet_entries(wallet_id, created_at)`.
- The simulated engine is deterministic per asset+seed so candles can be **computed**, not
  stored row-by-row, keeping the DB light.
- Settlement jobs are tiny and fast; ensure a queue worker is always running
  (`php artisan queue:work`).

---

## 9. Testing guidelines

- **Unit test** `SettlementService` win/lose/tie logic exhaustively (UP/DOWN × up/down/equal).
- **Unit test** `WalletService` for non-negative balance, correct debit/credit, ledger sum == balance.
- **Feature test** the place→settle flow with a fake clock and the SimulatedDriver.
- **Mock the BinanceLiveDriver** in tests; never hit the network in the test suite.
- Add a verification step before any release: run `php artisan test` + Pint.

See `06_MIGRATION_AND_ROADMAP.md` for the phased build order.
