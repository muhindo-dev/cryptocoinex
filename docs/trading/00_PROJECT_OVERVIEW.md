# Project Overview — Trading Learning Simulator

> **Working name:** CryptoCoinex Trading Trainer
> **Status:** Planning / design phase (no code yet)
> **Last updated:** 2026-06-10

---

## 1. What we are building

A **trading learning platform** where students practice fixed-time / "binary-style"
up-or-down trading in a realistic, risk-free environment. The product imitates the
core experience of platforms like **ExpertOption** — a live ticking candlestick chart,
an up/down prediction with a stake and an expiry timer, a virtual wallet, and instant
win/loss settlement — but exclusively for **education**, using **fake money only**.

Students learn the *mechanics* of reading a chart, timing entries, managing a balance,
and understanding risk, without ever risking real funds.

### The trade loop (the heart of the product)

```
1. Student picks an asset (e.g. BTC/USDT) and a timeframe.
2. A candlestick chart ticks live in front of them.
3. They choose UP or DOWN, set a stake, and pick an expiry (e.g. 60s).
4. The system locks the entry price + timestamp.
5. When the timer expires, the system compares the price then vs. entry.
6. Correct prediction -> payout credited to wallet. Wrong -> stake lost.
7. The trade is written to history; balance and stats update.
```

---

## 2. Why it is purely educational (and what that means)

The *real-money* version of fixed-time options is restricted or banned for retail users
in many jurisdictions (EU/ESMA, UK/FCA, and others) because it behaves like gambling.
**We deliberately avoid all of that.** This system:

- Uses **virtual balances only** — no deposits, no withdrawals, no real payouts.
- Is framed and marketed as a **learning tool / simulator**, not an investment service.
- Carries clear disclaimers that it is for practice and education.

This keeps the project legal, safe, and focused on its real goal: **turning aspiring
students into confident, knowledgeable traders.**

---

## 3. Who it is for

- **Primary users:** Students who aspire to become expert traders and want a realistic
  place to practice.
- **Secondary users:** Instructors / admins who manage students, grant practice
  balances, configure assets, run competitions, and monitor progress.

---

## 4. Data: two modes, one toggle

Every chart can be driven by one of two sources, switchable by the user with a single toggle:

| Mode | Source | Best for |
|------|--------|----------|
| **Simulated** (default) | Our own server-side price engine (random-walk / GBM) | Any asset, full control of volatility, always available, zero cost |
| **Live** | Free external market data (Binance public feed for crypto) | Practising on *real* market movement |

Live data is excellent and free for **crypto**; for **stocks/forex** truly-free real-time
data is scarce, so the **simulated engine is the primary mode** and live is the optional
"real market" experience. See `04_DATA_AND_ENGINE.md`.

---

## 5. How it relates to the existing project

The current `cryptocoinex` codebase is a **Laravel 12** application (originally a
legal-case + courses/LMS system). We are **not** starting from scratch. We **reuse**:

- The Laravel backend (routing, Eloquent ORM, queues, auth).
- The existing **admin shell** (Blade-based admin layout, controllers, auth middleware).
- Existing **assets** (CSS/JS in `public/`, Blade layouts, components).
- Patterns from existing finance models (`Account`, `Transaction`, `Payment`) as a
  benchmark for the new wallet/ledger.

The trading system is built as a **new module inside this Laravel app**. The frontend
stays jQuery + AJAX (the developer's comfort zone) plus a charting library. See
`01_DECISIONS.md` and `02_ARCHITECTURE.md`.

---

## 6. Definition of success (MVP)

The MVP is successful when a logged-in student can:

1. Open a trading screen with a **live ticking candlestick chart**.
2. **Toggle** between simulated and live (crypto) data.
3. Place an **UP/DOWN trade** with a stake and expiry.
4. See it **settle automatically** at expiry with a correct win/loss outcome.
5. See their **virtual wallet balance** and **trade history** update.
6. Have an **admin** able to manage students, assets, and grant practice balances.

---

## 7. Document map

| File | Purpose |
|------|---------|
| `00_PROJECT_OVERVIEW.md` | This file — vision, scope, success criteria |
| `01_DECISIONS.md` | Final, locked decision on every design fork + rationale |
| `02_ARCHITECTURE.md` | System design, layers, the data-source seam, coding guidelines |
| `03_FEATURES.md` | Core feature list and what to implement |
| `04_DATA_AND_ENGINE.md` | Simulation engine, live data drivers, the toggle, API research |
| `05_DATA_MODEL.md` | Database schema, models, migrations |
| `06_MIGRATION_AND_ROADMAP.md` | Tasks to convert the current app + phased build plan |
| `README.md` | Index of all docs |
