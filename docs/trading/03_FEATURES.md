# Core Features — What To Implement

> Prioritised. **MVP** = must-have for first usable release. **V2/V3** = follow-ons.
> Each feature notes where it lives (student vs admin) and key acceptance criteria.

Legend: 🟢 MVP · 🟡 V2 · 🔵 V3

---

## A. Student-facing — Trading

### 🟢 A1. Live candlestick chart
- TradingView Lightweight Charts rendering OHLC candles for the selected asset.
- Seeds with historical candles, then updates the forming candle in real time (~1s poll).
- Current price label + up/down color.
- **Accept:** chart loads <2s, updates smoothly without flicker, no console errors.

### 🟢 A2. Data-source toggle (Simulated ⟷ Live)
- A single switch on the trading screen. Persists per user (localStorage + server default).
- Switching reseeds the chart from the chosen driver.
- **Accept:** toggling changes the feed; trades opened reflect the active mode.

### 🟢 A3. Asset & timeframe selection
- Dropdown of admin-enabled assets (e.g. BTC/USDT, ETH/USDT, simulated "EUR/USD-SIM").
- Timeframe selector (e.g. 1s/5s/1m candles — start with 1m + a fast sim tick).
- **Accept:** only admin-enabled assets appear; switching asset reloads chart.

### 🟢 A4. Place an UP/DOWN trade
- Controls: direction (UP/DOWN), stake amount, expiry (e.g. 30s/60s/5m from allowed list).
- Validates stake vs. wallet balance and asset min/max.
- Shows a live countdown for each open trade and a marker on the chart at entry.
- **Accept:** stake is debited on open; invalid stakes are rejected with a clear message.

### 🟢 A5. Automatic settlement & result
- At expiry the trade settles server-side; UI animates WIN (payout) or LOSS.
- Payout = stake + stake × payout% on win; stake lost on loss; configurable tie policy.
- **Accept:** outcome matches entry vs exit price; wallet updates exactly once.

### 🟢 A6. Open positions & trade history
- Panel of currently open trades (with countdown) and a paginated history of settled trades
  (asset, direction, stake, entry, exit, result, P/L, time).
- **Accept:** history is accurate and matches the wallet ledger.

### 🟡 A7. Quick stats / performance
- Win rate, total trades, net virtual P/L, best/worst streak.

### 🟡 A8. Multiple concurrent trades
- Allow several open trades at once across assets, each settling independently.

### 🔵 A9. Indicators & drawing tools
- Moving averages, RSI overlay; basic trend-line drawing for teaching technical analysis.

---

## B. Student-facing — Wallet & Account

### 🟢 B1. Virtual wallet
- Balance display; everything derived from the ledger.
- Starting practice balance granted on registration (admin-configurable amount).
- **Accept:** balance always equals sum of ledger entries; never negative.

### 🟢 B2. Ledger / transaction history
- List of every wallet entry: stake holds, payouts, admin top-ups, resets.

### 🟡 B3. Reset / new practice round
- Student can reset their virtual balance to the default (optionally admin-gated).

### 🔵 B4. Achievements / badges
- Gamified milestones (first win, 10-trade streak, etc.) to aid learning motivation.

---

## C. Learning layer (the "become an expert" part)

### 🟡 C1. Guided lessons / tooltips
- Inline explanations of candles, spreads, expiry, risk — surfaced contextually.
- Reuse the existing **Course/Enrollment** models as the lesson backbone if useful.

### 🟡 C2. Practice scenarios
- Admin-defined scenarios (e.g. "volatile market", "trending up") via simulation parameters,
  so students practise specific conditions.

### 🔵 C3. Strategy journal
- Students annotate trades with their reasoning; review later to learn from mistakes.

### 🔵 C4. Leaderboards & competitions
- Time-boxed contests on virtual P/L to drive engagement (clearly fake-money only).

---

## D. Admin-facing (reuse existing admin shell)

### 🟢 D1. Asset management
- CRUD for tradable assets: symbol, display name, class (crypto/forex/stock/sim),
  payout %, min/max stake, allowed expiries, live-source mapping, simulation params, enabled flag.

### 🟢 D2. Student management
- View students, their wallet balance, trade history; grant/reset practice balances.
- Largely extends the existing user/student admin.

### 🟢 D3. Global settings & feature flags
- Default starting balance, default mode, enable/disable live mode globally, tie policy.

### 🟡 D4. Monitoring & reports
- Volume of trades, active students, win-rate distribution, engagement — reuse the existing
  reports/dashboard patterns.

### 🟡 D5. Scenario / simulation control
- Tune volatility/drift per asset; create teaching scenarios (supports C2).

### 🔵 D6. Competition management
- Create/run leaderboards and contests (supports C4).

---

## E. Cross-cutting / non-functional

- 🟢 **Auth**: reuse existing login; students vs admins via existing role system.
- 🟢 **Disclaimers**: persistent "Practice / educational simulator — not real money / not
  financial advice" notice.
- 🟢 **Responsive UI**: trading screen usable on laptop + tablet; mobile-friendly later.
- 🟡 **Internationalisation-ready** copy (audience may be multilingual).
- 🟢 **Audit trail**: every wallet mutation is logged via the ledger.

---

## MVP checklist (ship this first)

```
[ ] A1  Live candlestick chart
[ ] A2  Simulated/Live toggle
[ ] A3  Asset + timeframe selection
[ ] A4  Place UP/DOWN trade
[ ] A5  Auto settlement + result
[ ] A6  Open positions + history
[ ] B1  Virtual wallet
[ ] B2  Ledger history
[ ] D1  Admin asset management
[ ] D2  Admin student management
[ ] D3  Global settings / feature flags
[ ] E   Auth + disclaimers + audit trail
```
