# Final Decisions

> Every major fork, decided. Each entry: **the decision**, the alternatives considered,
> and **why**. Treat these as settled unless explicitly revisited.

---

## D1 — Product type: educational simulator (fake money only)

**Decision:** Build a **pure learning simulator**. Virtual balances only. No real money,
no deposits, no withdrawals, no real payouts — ever.

**Why:** Real-money fixed-time options are legally restricted/banned for retail in many
regions and carry licensing burdens. The educational goal is fully served by a simulator,
and it keeps us legal and safe. Real money is explicitly **out of scope**, permanently.

---

## D2 — Build location: a module inside the existing Laravel app

**Decision:** Build the trading system as a **new module inside the existing Laravel 12
app** (`cryptocoinex`), reusing its admin shell, auth, ORM, queues, layouts, and assets.

**Alternatives considered:** A separate plain-PHP application.

**Why:** The reuse the developer wants (admin + assets) is **Laravel-specific**. A plain-PHP
rewrite would discard exactly what we want to keep and rebuild auth/admin/ORM by hand. Keeping
Laravel as the backend costs nothing on the frontend — see D3.

---

## D3 — Stack: Laravel backend + jQuery/AJAX frontend

**Decision:**
- **Backend:** Laravel 12 / PHP 8.2 (existing). Eloquent for data, queues for settlement,
  controllers exposing JSON endpoints.
- **Frontend:** Blade templates + **jQuery + AJAX** + vanilla JS. No SPA framework.
- **Charting:** **TradingView Lightweight Charts** (see D6).

**Why:** This honours the developer's preference for jQuery/AJAX on the frontend while
still reusing the Laravel backend. "Plain PHP" was the original idea, but Laravel *is* the
backend the assets/admin already live in — so we keep it and write jQuery against its JSON
endpoints, which is effectively the same developer experience on the client side.

---

## D4 — Data: both modes, simulated as primary, user toggle

**Decision:** Support **both** a **simulated** data engine and **live** external data,
switchable by the user via a toggle. **Simulated is the default/primary** mode.

**Why:** Simulated data is free, always available, fully controllable, and works for any
asset. Live data is great for realism but only reliably free for crypto. Defaulting to
simulated guarantees the product always works; live is the "real market" bonus.

---

## D5 — Live data provider: Binance public feed (crypto first)

**Decision:** For live mode, use **Binance public market data** first:
- **REST** klines (`/api/v3/klines`) for historical candles to seed the chart.
- **WebSocket** kline/trade streams (`wss://stream.binance.com`) for live ticks.
- **No API key required**, free, real-time.

Stocks/forex live data is **deferred** (Phase 4+) and would use a free-tier keyed provider
(Finnhub or Twelve Data) with rate limits. Until then, stocks/forex run on the simulated engine.

**Why:** Crypto is the only asset class with truly-free, keyless, real-time data. Start
where the data is best; simulate the rest.

---

## D6 — Charting library: TradingView Lightweight Charts

**Decision:** Use **TradingView Lightweight Charts** (Apache-2.0, ~45 KB, HTML5 canvas).

**Alternatives considered:** Chart.js (not purpose-built for live candlesticks),
ApexCharts (heavier), Highcharts (commercial license).

**Why:** Purpose-built for financial candlesticks, free/open-source, tiny, 60+ FPS with
live updates, works with plain JS (no framework needed). **License note:** requires a
visible "TradingView" attribution link on the page — we will include it.

---

## D7 — Real-time delivery: AJAX polling first, WebSocket-ready later

**Decision:** Start with **~1-second AJAX polling** for live price/candle updates. Design
the data-source layer so a true **WebSocket push** (Laravel Reverb) can be added later
**without changing the frontend contract**.

**Why:** Polling is simple, matches the jQuery/AJAX approach, and is perfectly adequate for
a learning sim. WebSockets add infra complexity we don't need on day one. The abstraction
seam (see `02_ARCHITECTURE.md`) means upgrading later is a backend swap, not a rewrite.

---

## D8 — Trade settlement: server-authoritative, queued jobs

**Decision:** All trade logic is **server-side and authoritative**. Entry price, expiry,
and outcome are computed by the server. Settlement runs via a **queued job** scheduled for
the expiry time. The client never decides outcomes.

**Why:** Prevents cheating, keeps the wallet ledger trustworthy, and uses Laravel's queue
system (already configured) cleanly.

---

## D9 — Wallet: virtual, double-entry ledger

**Decision:** Each student has a **virtual wallet** with an append-only **ledger** of
entries (stake debit, payout credit, admin top-up). Balance is derived from the ledger.

**Why:** A ledger is auditable, prevents balance drift, and mirrors the existing
`Account`/`Transaction` pattern in the codebase. See `05_DATA_MODEL.md`.

---

## D10 — Asset configuration: admin-managed

**Decision:** Tradable assets (symbol, display name, class, payout %, min/max stake,
allowed expiries, live-source mapping, simulation parameters) are **configured in the admin
panel**, not hard-coded.

**Why:** Lets instructors curate the learning environment and tune difficulty without code
changes.

---

## Decisions summary table

| # | Topic | Decision |
|---|-------|----------|
| D1 | Product type | Educational simulator, fake money only |
| D2 | Build location | Module inside existing Laravel app |
| D3 | Stack | Laravel backend + jQuery/AJAX frontend |
| D4 | Data modes | Both; simulated primary; user toggle |
| D5 | Live provider | Binance public feed (crypto first) |
| D6 | Charting | TradingView Lightweight Charts |
| D7 | Real-time | AJAX polling now, WebSocket-ready later |
| D8 | Settlement | Server-authoritative, queued jobs |
| D9 | Wallet | Virtual, double-entry ledger |
| D10 | Assets | Admin-configured |
