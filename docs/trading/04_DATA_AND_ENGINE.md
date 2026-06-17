# Data Sources & The Price Engine

> How candles get to the chart in both modes, the math of the simulator, the live Binance
> integration, and the API research behind the choices.

---

## 1. The two drivers (recap)

Both implement `MarketDataDriver` (see `02_ARCHITECTURE.md` §3). The rest of the system is
identical regardless of which is active.

| | SimulatedDriver | BinanceLiveDriver |
|---|---|---|
| Cost | Free | Free |
| API key | None | None |
| Assets | Any (incl. fake symbols) | Crypto pairs (BTCUSDT, ETHUSDT, …) |
| Availability | Always | Depends on upstream |
| Control | Full (volatility, drift, scenarios) | None (real market) |
| Use | Default / teaching | "Real market" practice |

---

## 2. Simulated price engine

### 2.1 Model: Geometric Brownian Motion (GBM)

Standard model for price paths. Discrete step:

```
S(t+Δt) = S(t) * exp( (μ − σ²/2)·Δt + σ·√Δt · Z )
```

- `S` = price, `μ` = drift (trend), `σ` = volatility, `Δt` = time step, `Z` ~ N(0,1).
- Per-asset config sets `S0` (start price), `μ`, `σ`, and tick interval.
- A simpler **bounded random walk** is an acceptable alternative for very fast ticks;
  GBM is preferred because prices stay positive and look realistic.

### 2.2 Determinism via seeding (key design point)

Seed the RNG with `hash(assetId, sessionEpoch)`. Because the path is deterministic given the
seed + elapsed time, the server can **compute** the price/candle for any timestamp **without
storing every tick**. This keeps the DB light and lets multiple requests agree on "the price
now."

```
priceAt(asset, t) = deterministic_GBM(seed=asset.seed, from=asset.epoch, to=t, params)
```

`currentPrice()` = `priceAt(asset, now)`. `candles()` = aggregate computed ticks into OHLC
buckets for the requested interval. `latestCandle()` = the current forming bucket.

### 2.3 Building candles from ticks

For interval `I` (e.g. 60s): bucket ticks by `floor(t / I)`. Each bucket's
`open` = first tick, `high`/`low` = max/min, `close` = last tick. The current bucket keeps
updating until `I` elapses, then a new candle starts — exactly what the chart animates.

### 2.4 Scenarios (teaching control)

Admin presets adjust `μ`/`σ` to create conditions:

| Scenario | μ (drift) | σ (volatility) |
|----------|-----------|----------------|
| Calm / ranging | ~0 | low |
| Strong uptrend | positive | medium |
| Strong downtrend | negative | medium |
| Volatile / choppy | ~0 | high |

This supports features C2/D5 in `03_FEATURES.md`.

> **Fairness note:** keep the simulation an honest random process. Do **not** secretly bias
> outcomes against the student — the goal is genuine skill-building, and the wallet is fake
> anyway, so there is no reason to rig it.

---

## 3. Live driver — Binance public API

### 3.1 Why Binance
Truly free, **no API key**, real-time, deep history, simple JSON. Best free real-time source
for any asset class. Crypto only — which is fine; everything else uses the simulator.

### 3.2 Endpoints

**Historical candles (seed the chart) — REST:**
```
GET https://api.binance.com/api/v3/klines?symbol=BTCUSDT&interval=1m&limit=200
-> [ [openTime, open, high, low, close, volume, closeTime, ...], ... ]
```

**Live updates — WebSocket (kline stream):**
```
wss://stream.binance.com:9443/ws/btcusdt@kline_1m
-> { e:"kline", k:{ t:openTime, o,h,l,c, x:isClosed, ... } }
```

**Current price — REST (lightweight):**
```
GET https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT  -> { price:"67000.12" }
```

### 3.3 Server-side caching (important)

PHP request/response can't hold a persistent WebSocket per user. Two viable patterns:

- **MVP — REST + cache (recommended to start):** A small server process / scheduled task
  (or a cache-warming endpoint) polls Binance REST every ~1–2s and writes the latest candle
  + price into **cache (Redis/file)**. The student-facing `/trade/feed` reads from cache only.
  This decouples user polling from Binance and respects rate limits (one upstream call serves
  all users).
- **Later — WebSocket ingestor:** A standalone PHP CLI worker (or Node helper) holds the
  Binance WS connection, pushing ticks into cache / broadcasting via Laravel Reverb. Same
  cache contract, lower latency. Drop-in because of the driver seam.

### 3.4 Rate limits & resilience
- Binance REST allows generous weight-based limits; one shared poller stays well within them.
- On upstream error/timeout: serve last cached value; if stale beyond a threshold, surface a
  "live feed unavailable" state and (per D3 feature flag) optionally fall back to simulated.
- Never call Binance directly from the student request path.

---

## 4. Stocks & forex (deferred — Phase 4+)

Truly-free **real-time** equity/forex data effectively doesn't exist; providers gate it
behind keys and tight limits. Plan:

| Provider | Free tier | Notes |
|----------|-----------|-------|
| **Finnhub** | WebSocket, stocks/forex/crypto | API key; rate-limited; good first choice |
| **Twelve Data** | ~170 ms WS, 800 req/day | API key; streaming limited on free |
| **FCS API** | forex/crypto/stocks | API key; limited |
| Alpha Vantage | ~25 req/day | Too limited for real-time |

Until a provider is added, **stocks/forex assets run on the SimulatedDriver** with realistic
parameters. Adding a real provider later = a new driver behind the same interface.

---

## 5. Frontend feed contract

The browser is mode-agnostic; it only knows these endpoints:

```
GET  /trade/feed?asset=BTCUSDT&interval=1m&mode=sim|live&limit=200
       -> { candles:[{time,open,high,low,close}, ...], price: 67000.12, mode, serverTime }

GET  /trade/price?asset=BTCUSDT&mode=sim|live
       -> { price: 67000.12, serverTime }
```

`time` is a UNIX timestamp (seconds) to match Lightweight Charts. Initial load uses `candles`;
the ~1s poll uses `latestCandle`/`price` to update the forming candle.

---

## 6. Library / dependency notes

- **TradingView Lightweight Charts** — Apache-2.0. Load via CDN or npm. **Must show a
  visible "TradingView" attribution link** on any page using it (license requirement).
- **jQuery** — already viable in the project's asset pipeline; used for AJAX + DOM.
- **Predis / phpredis** — recommended for the live-data cache (or Laravel's file/database
  cache for a simpler MVP).
- No paid dependencies anywhere in the MVP.

---

## 7. Sources (API research)

- Binance public API: https://github.com/binance/binance-spot-api-docs
- TradingView Lightweight Charts: https://github.com/tradingview/lightweight-charts
- Finnhub: https://finnhub.io/
- Twelve Data: https://twelvedata.com/
- CoinPaprika: https://coinpaprika.com/api/
- FCS API: https://fcsapi.com/
- Best Free Crypto API 2026 (comparison): https://coinmarketcap.com/academy/article/best-free-crypto-api-in-2026-free-tier-comparison
- Alpha Vantage: https://www.alphavantage.co/
