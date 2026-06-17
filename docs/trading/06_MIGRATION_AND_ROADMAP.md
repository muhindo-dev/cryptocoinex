# Migration Plan & Build Roadmap

> How we convert the current `cryptocoinex` (legal/LMS) app into the Trading Trainer,
> and the phased order in which we build. **No code is written until this plan is approved.**

---

## Part 1 — Current state (what exists today)

The app is a **Laravel 12 / PHP 8.2** project, currently a legal-case + courses/LMS system:

- **Auth:** Laravel Breeze; admin login at `/admin/login`; `auth` + `admin` middleware.
- **Admin shell:** custom Blade admin under `resources/views/admin/*`, controllers under
  `app/Http/Controllers/Admin/*`, routes grouped under `/admin`.
- **Legacy domain models:** `LegalCase`, `CaseNote`, `Client`, `Document`, `Course`,
  `Instructor`, `Student`, `Enrollment`, `Payment`, `Account`, `Transaction`, `FinancialPeriod`,
  `Chat`, `Message`.
- **Finance pattern to benchmark:** `Account` + `Transaction` + `Payment` — a good template
  for the new wallet/ledger.
- **Assets:** `public/css`, `public/js`, images, Blade layouts/components.
- **Queues** available (Laravel) — used for settlement.

### What we KEEP / REUSE
- Laravel backend, routing, Eloquent, queues.
- Admin layout/shell, auth, `admin` middleware, navigation patterns.
- CSS/JS asset pipeline and shared Blade components/layouts.
- `Course`/`Enrollment` models — optionally repurposed for the learning layer (features C1).
- Finance models as a **reference** for the wallet ledger design.

### What we ADD (net-new, isolated under a Trading namespace)
- `App\Models\Trading\*`, `App\Services\Trading\*`, `App\Http\Controllers\Trading\*`,
  `App\Http\Controllers\Admin\Trading\*`, `App\Jobs\Trading\*`.
- New `trading_*` tables (see `05_DATA_MODEL.md`).
- Student trading UI + jQuery module + Lightweight Charts.

### What we RETIRE / IGNORE (decide per item)
- Legal-case features (`LegalCase`, `CaseNote`, `Client`, `Document`, `LegalCase` admin
  screens) are **not needed** for trading. Options:
  - **(a) Leave dormant** — keep tables/routes but hide from nav (fastest, lowest risk). ✅ recommended for now
  - **(b) Remove later** — once the trading app is stable, prune legal routes/views/models in a dedicated cleanup pass.
- Do **not** delete anything in the first phases; hide it from navigation and focus on building.

> **Branching:** do all of this on a new git branch (e.g. `feature/trading-trainer`) so the
> legacy app remains intact and revertible.

---

## Part 2 — Conversion tasks (high level)

```
T0  Create branch `feature/trading-trainer`; add docs/ (this folder). ✅ docs done
T1  Rebrand shell: app name, logo (logo-square/horizontal already present), nav cleanup;
    hide legal/LMS menu items behind a flag.
T2  DB: add trading_* migrations + models + TradingSeeder (demo assets, settings).
T3  Wallet: WalletService (ledger debit/credit, balance, invariants) + tests.
T4  Data seam: MarketDataDriver interface + SimulatedDriver (GBM) + MarketDataManager.
T5  Feed endpoints: FeedController (/trade/feed, /trade/price) reading from driver/cache.
T6  Frontend chart: Blade trading page + trade.js + Lightweight Charts, polling the feed.
T7  Toggle: simulated/live switch wired to the feed (live still stubbed/sim at this point).
T8  Trade engine: TradeService (open), SettleTradeJob + SettlementService (+ exhaustive tests).
T9  Trade UI: place UP/DOWN, countdown, open positions, history, result animation.
T10 Live driver: BinanceLiveDriver + cache warmer (REST poll → cache); enable live toggle for crypto.
T11 Admin: asset management, student/wallet management, global settings (reuse admin shell).
T12 Polish: disclaimers, stats, responsive pass, empty/error states.
T13 Verify: full test suite + Pint + manual QA of place→settle in both modes.
```

---

## Part 3 — Phased roadmap

### Phase 0 — Planning ✅ (this document set)
Decisions locked; docs written. **Gate:** developer approves before any code.

### Phase 1 — Foundation (T1–T3)
Branch, shell rebrand + nav cleanup, trading tables/models/seeder, wallet service with tests.
**Deliverable:** a student has a wallet with a starting balance; admin can see it.
**Gate:** wallet invariants pass tests.

### Phase 2 — Simulated trading MVP (T4–T9)
Data seam + SimulatedDriver, feed endpoints, live chart, toggle (UI only), trade engine +
settlement, trade UI.
**Deliverable:** a student can place and auto-settle UP/DOWN trades on simulated data, wallet
updates, history shows. **This is the first genuinely usable product.**
**Gate:** place→settle works end-to-end; settlement logic fully unit-tested.

### Phase 3 — Live crypto data (T10)
BinanceLiveDriver + cache warmer; live toggle actually switches crypto assets to real data.
**Deliverable:** student toggles to live and trades on real BTC/USDT movement.
**Gate:** live feed cached, resilient, falls back to sim on failure.

### Phase 4 — Admin & curation (T11)
Asset CRUD, student/wallet admin, global settings & feature flags, scenario params.
**Deliverable:** instructors run the environment without touching code.

### Phase 5 — Learning & polish (T12 + selected V2 features)
Disclaimers, stats, lessons/tooltips, scenarios, responsive polish.
**Deliverable:** the "become an expert" layer; production-ready feel.

### Phase 6 — Stretch (V3)
Indicators/drawing tools, leaderboards/competitions, achievements, stocks/forex live providers,
WebSocket push via Laravel Reverb.

---

## Part 4 — Cross-phase definition of done

A phase is "done" only when:
1. Its features meet the acceptance criteria in `03_FEATURES.md`.
2. New business logic has unit/feature tests; `php artisan test` is green.
3. `./vendor/bin/pint` passes (PSR-12).
4. Wallet invariants (`05_DATA_MODEL.md` §5) hold.
5. A manual QA pass of the trade loop succeeds in the relevant mode(s).

---

## Part 5 — Risks & mitigations

| Risk | Mitigation |
|------|------------|
| Live feed downtime/rate limits | Cache + shared poller; feature-flag fallback to simulated |
| Float rounding in wallet | Integer virtual units; ledger invariants + tests |
| Settlement double-credit | Idempotent job guarded on `status==open`; DB transaction |
| Cheating via client | Server-authoritative entry/exit; validate all inputs |
| Scope creep | Strict phase gates; V2/V3 features deferred |
| Legacy code interference | Isolate under Trading namespace; leave legal/LMS dormant, not deleted |
| Regulatory perception | Fake money only + persistent educational disclaimers (D1) |

---

## Part 6 — Open questions for the developer

1. **Legacy features:** confirm we *hide* (not delete) the legal/LMS screens for now? (recommended)
2. **Starting balance:** default practice balance amount? (e.g. 10,000 PRACTICE$)
3. **Cache backend:** Redis available on the server, or start with Laravel file/database cache?
4. **First assets:** which symbols to seed? (suggest BTCUSDT, ETHUSDT live + one sim forex)
5. **Branding:** keep the `cryptocoinex` name + existing logos, or rebrand?

Answer these and Phase 1 can begin.
