# Cryptocoinex — Master Improvement Plan
> Branch: `feature/trading-trainer` · Laravel 12 / PHP 8.4.7 · MAMP `localhost:8888/cryptocoinex`
> Last audited: 2026-06-11

---

## ⚠️ DEVELOPMENT ENVIRONMENT NOTICE

```
╔══════════════════════════════════════════════════════════════════════════╗
║  THIS IS A 100% DEVELOPMENT / DEMO ENVIRONMENT                          ║
║                                                                          ║
║  • All user accounts, trades, balances, and data are SYNTHETIC / DUMMY  ║
║  • "PRACTICE$" is a virtual currency with ZERO real-world value         ║
║  • No payment processing, no real money, no financial services          ║
║  • All prices are either simulated (GBM model) or cached Binance data   ║
║  • Data can be added, modified, or wiped freely during development      ║
║  • Every completed feature MUST have ≥ 50 seeded dummy records          ║
╚══════════════════════════════════════════════════════════════════════════╝
```

---

## 1. Current State Audit

### 1.1 What Is Fully Working ✅

| Area | Status | Notes |
|------|--------|-------|
| SimulatedDriver (GBM price engine) | ✅ | Two-level coarse/fine, deterministic RNG (31-bit LCG), seed-stable |
| BinanceLiveDriver | ✅ | Fetches via HTTP + Redis cache, falls back to sim |
| MarketDataManager (driver factory) | ✅ | Clean interface seam |
| WalletService (double-entry ledger) | ✅ | Integer amounts, no floats, debit guard |
| TradeService | ✅ | Validation, entry price lock, SettleTradeJob dispatch |
| SettlementService | ✅ | Idempotent, FOR UPDATE lock, refund/tie policy |
| SettleTradeJob | ✅ | Database queue, 3 retries, 5s backoff |
| Admin dashboard | ✅ | Trading-focused stats, no legacy data |
| Admin: Assets CRUD | ✅ | Create, edit, enable/disable |
| Admin: Students list + show | ✅ | Balance, trade history, topup, reset |
| Admin: Trading settings | ✅ | Start balance, mode, tie policy, student reset |
| Admin: Trading overview | ✅ | 6 stat cards, recent trades, asset volume |
| Admin sidebar | ✅ | Trading-only nav, Cryptocoinex branding |
| Trading screen (student) | ✅ | Dark mode, one-click BUY/SELL, LightweightCharts local |
| Chart: candles + live price poll | ✅ | 1s poll, ResizeObserver, local lib |
| Wallet page | ✅ | Balance, ledger entries, reset |
| Auth (admin login) | ✅ | Dark trading theme |
| Queue worker | ✅ | Running, 0 stuck jobs |
| Unit tests (SimulatedDriver) | ✅ | 12 tests, all passing |

### 1.2 What Is Incomplete / Broken 🔴

| Area | Issue | Priority |
|------|-------|----------|
| **Student registration/onboarding** | No dedicated onboarding flow; students land on generic Laravel register | P0 |
| **Student profile page** | No profile page; can't edit name, phone, avatar, timezone, experience | P0 |
| **Leaderboard** | Not built — no ranking page, no public stats | P1 |
| **Notifications** | No in-app or email notification when a trade settles | P1 |
| **Admin analytics charts** | Only raw numbers on dashboard; no visual charts/graphs | P1 |
| **More assets** | Only 3 assets; no gold, silver, indices, more forex | P1 |
| **Achievements/Badges** | Not designed or built | P2 |
| **Tournament mode** | Not designed or built | P2 |
| **Binance cache warmer scheduling** | `WarmLiveCache` command exists but is NOT scheduled in `Kernel` | P1 |
| **Mobile responsive trading screen** | Bottom controls clip on small screens | P1 |
| **Chart indicators** | No MA, RSI, MACD, Bollinger Bands toggles | P2 |
| **Trading history page** (full) | Only sidebar panel; no full paginated history page | P1 |
| **Admin users list** | Still shows legacy ONYX officers; role labels say "Legal Officer" | P1 |
| **Legacy code cleanup** | LegalCase, Client, Document controllers/views/routes still active | P3 |
| **Email notifications** | WelcomeCredentials email uses old template tokens | P2 |
| **PWA manifest** | No manifest.json, no service worker, no install prompt | P3 |
| **Dark mode toggle (student)** | No light/dark preference persisted | P3 |

### 1.3 What Needs Improvement 🟡

| Area | Issue |
|------|-------|
| Stake amount UX | Raw number input; no currency formatting, no % of balance shown |
| Balance display | Shows raw integer; needs locale formatting with separator |
| Trade result feedback | Toast only; no visual animation (confetti for win, shake for loss) |
| Expiry timer | Counts down per selection but doesn't sync with server time |
| Open trade P&L | Shows "Winning/Losing" text only; should show estimated $ P&L |
| Asset icons | No logos/icons for BTC, ETH, EUR/USD |
| User role labels | "Legal Officer", "Front Desk" — should be trading-specific |
| Admin user creation | Cannot set initial password from UI |
| Queue visibility | No way to see queue depth from admin panel |

---

## 2. Money & Amount UX Improvements

The stake input is the most-used element on the trading screen. It must feel professional.

### 2.1 Stake Input Redesign

**Current:** Plain `<input type="number" value="100">`

**Improved:**
- Show PRACTICE$ symbol inline: `$ 100`
- Display remaining balance percentage below: `(10% of balance)`
- Color the percentage indicator: green < 25%, amber 25–50%, red > 50%
- Quick-stake buttons as % of balance: **5%**, **10%**, **25%**, **50%**, **All-in**
- Keep absolute quick-stakes (50, 100, 250) as secondary option
- Keyboard shortcut: `↑/↓` arrows increase/decrease by min_stake
- Auto-cap at max_stake with visual feedback (shake animation)
- Show minimum stake below input: "Min: 1 PRACTICE$"

**Implementation:**
```js
// Balance percentage indicator
const pct = Math.round((stake / balance) * 100);
stakePctEl.textContent = `${pct}% of balance`;
stakePctEl.style.color = pct > 50 ? '#ef4444' : pct > 25 ? '#f59e0b' : '#22c55e';
```

### 2.2 Balance Display

**Current:** `10,000` (raw formatted integer)

**Improved:**
- Show currency label inline: `10,000 PRACTICE$`
- Animate balance change: slide up/down on debit/credit
- Show daily P&L below balance: `Today: +240 (▲2.4%)`
- Color: gold when positive P&L, red when negative

### 2.3 Payout Visualization

**Current:** Percentage shown on button (e.g., "80%")

**Improved:**
- Show potential payout: `Profit: +80 PRACTICE$` (calculated live from stake)
- Show total return: `Return: 180 PRACTICE$`
- Add a visual payout bar showing risk/reward

### 2.4 Trade History P&L

- Each trade in history shows net P&L with color coding
- Running total P&L (all-time, today, this week)
- Win/Loss/Tie breakdown as mini donut chart in right panel

---

## 3. UI/UX Overhaul Plan

### 3.1 Design System Tokens (Dark Theme)

Define a consistent token set used everywhere:

```css
/* Background layers */
--bg-base:     #07090e;   /* page background */
--bg-surface:  #0d1117;   /* cards, panels */
--bg-elevated: #111821;   /* dropdowns, tooltips */
--bg-hover:    #161e2c;   /* hover states */

/* Borders */
--border:      #1c2a3a;
--border-focus:#2a3f5a;

/* Text */
--text-primary: #e2e8f0;
--text-muted:   #64748b;
--text-dim:     #334155;

/* Semantic */
--green:  #00c97b;  --green-muted:  rgba(0,201,123,.15);
--red:    #f53b57;  --red-muted:    rgba(245,59,87,.15);
--gold:   #f59e0b;  --gold-muted:   rgba(245,158,11,.15);
--blue:   #3b82f6;  --blue-muted:   rgba(59,130,246,.15);

/* Typography scale */
--text-xs:  .625rem;   /* 10px — labels, badges */
--text-sm:  .75rem;    /* 12px — meta text */
--text-base:.875rem;   /* 14px — body */
--text-lg:  1rem;      /* 16px — headings */
--text-xl:  1.25rem;   /* 20px — section titles */
--text-2xl: 1.5rem;    /* 24px — balance display */
--text-3xl: 2rem;      /* 32px — big numbers */
```

### 3.2 Trading Screen Improvements

**Top Bar**
- Add asset-class badge next to symbol: `CRYPTO`, `FOREX`, `SIM`
- Show 24h change percentage with color
- Add a mini sparkline (5-minute line) next to price
- Connection dot should pulse when live

**Chart Area**
- Add chart type toggle: Candles ↔ Line ↔ Area
- Add volume bars at bottom (if data available)
- Crosshair tooltip shows OHLC + volume
- Entry line on chart for each open trade (already exists, polish needed)

**Bottom Controls**
- Add "% of balance" indicator below stake input
- Show potential profit on each BUY/SELL button: `BUY ▲ | +80 PRACTICE$`
- After placing trade: animate the button briefly (pulse green/red)
- Expiry timer: make it more prominent, add circular progress indicator

**Right Deals Panel**
- Open position: show a live mini price bar (entry vs current)
- Add progress ring for time remaining on each open trade
- History: add date grouping (Today, Yesterday, This Week)
- Add "Export history" button (CSV download)

### 3.3 Admin Panel Improvements

**Dashboard**
- Replace raw numbers with ApexCharts: trades/day area chart, win-rate donut
- Add "Today's Activity" feed (last 10 events with timestamps)
- Add queue depth indicator (jobs pending)

**Students Page**
- Add avatar column
- Add last-active timestamp
- Add quick-action buttons inline (topup, view trades)
- Add bulk select + bulk topup/reset

**Assets Page**
- Add asset icon/logo upload
- Show current simulated price live
- Add enable/disable toggle inline (no page reload)

### 3.4 Loading States

Every async operation must show a loading state:
- Chart loading: skeleton shimmer in chart area
- Price polling: subtle pulse on price element
- Trade placing: button spinner, opacity reduction
- History loading: skeleton rows (3 placeholder rows)
- Feed loading: "Fetching market data…" overlay on chart

### 3.5 Error States

- Feed error: large centered error card with retry button in chart area
- Place trade error: highlighted message below BUY/SELL button (not just toast)
- Network offline: banner at top "No internet connection — prices paused"
- Session expired: modal with login redirect (not silent 401)

### 3.6 Micro-animations

- Win trade: green flash + confetti particles in right panel
- Loss trade: red flash + shake animation on balance
- Balance update: slide-up number transition
- Price tick up: green flash on price, tick down: red flash
- Tab switch: smooth fade between Open/History panels
- Button press: scale(0.97) + shadow reduction

---

## 4. Package Recommendations

### 4.1 Backend (Composer)

| Package | Purpose | Priority |
|---------|---------|----------|
| `spatie/laravel-activitylog` | Audit trail for trades, wallet ops, admin actions | P0 |
| `spatie/laravel-permission` | Replace simple `role` column with proper RBAC | P1 |
| `laravel/horizon` | Queue monitoring dashboard at `/horizon` | P1 |
| `barryvdh/laravel-debugbar` | Dev-only debug bar (routes, queries, timing) | P1 |
| `spatie/laravel-backup` | Automated DB + file backups | P2 |
| `laravel/telescope` | Request/query/job inspection at `/telescope` | P2 |
| `intervention/image` | Avatar resize/crop on upload | P1 |
| `maatwebsite/excel` | Trade history CSV/XLSX export | P2 |
| `laravel/sanctum` | Token auth for future mobile app / API | P2 |
| `propaganistas/laravel-phone` | Phone number validation and formatting | P2 |
| `league/commonmark` | Render markdown in trade notes / announcements | P3 |

**Install commands:**
```bash
composer require spatie/laravel-activitylog spatie/laravel-permission
composer require laravel/horizon --dev  # or production if monitoring needed
composer require barryvdh/laravel-debugbar --dev
composer require intervention/image
composer require maatwebsite/excel
```

### 4.2 Frontend (Local Vendor / npm)

| Library | Purpose | Priority |
|---------|---------|----------|
| **ApexCharts** v3 | Admin analytics: area charts, donut, heatmap | P0 |
| **Alpine.js** v3 | Reactive UI without Vue/React (modals, toggles, dropdowns) | P1 |
| **Animate.css** | Win/loss animations, entrance effects | P2 |
| **Toastify.js** | Replace custom toast system with polished library | P2 |
| **noUiSlider** | Stake amount slider with range visualization | P2 |
| **Day.js** | Date formatting/manipulation in JS (tiny, replaces moment) | P1 |
| **Canvas-Confetti** | Win celebration confetti effect | P2 |
| **Howler.js** | Trade sound effects (win chime, lose sound, tick) | P3 |
| **Driver.js** | Guided tour / onboarding walkthrough for new students | P2 |
| **Sortable.js** | Already have it — use for dashboard widget reordering | P3 |

**Note:** All libraries must be downloaded to `public/vendor/` — zero CDN in production.

---

## 5. Database Schema Enhancements

### 5.1 `users` Table — New Columns

```sql
-- Migration: 2026_06_12_000001_enhance_users_for_trading_profiles.php
ALTER TABLE users ADD COLUMN date_of_birth       date          NULL AFTER email;
ALTER TABLE users ADD COLUMN gender              varchar(30)   NULL AFTER date_of_birth;
ALTER TABLE users ADD COLUMN country             varchar(100)  NULL AFTER gender;
ALTER TABLE users ADD COLUMN city                varchar(100)  NULL AFTER country;
ALTER TABLE users ADD COLUMN timezone            varchar(60)   NULL DEFAULT 'Africa/Kampala';
ALTER TABLE users ADD COLUMN trading_experience  varchar(30)   NULL DEFAULT 'beginner';
ALTER TABLE users ADD COLUMN preferred_assets    json          NULL;
ALTER TABLE users ADD COLUMN notification_prefs  json          NULL;
ALTER TABLE users ADD COLUMN last_active_at      timestamp     NULL;
ALTER TABLE users ADD COLUMN bio                 text          NULL;
ALTER TABLE users ADD COLUMN cover_photo         varchar(500)  NULL;
ALTER TABLE users ADD COLUMN twitter_handle      varchar(100)  NULL;
ALTER TABLE users ADD COLUMN instagram_handle    varchar(100)  NULL;
```

**Fields by use case:**
- `date_of_birth`, `gender`, `country`, `city` — full identity profile
- `timezone` — personalized time display (expiry countdowns, history timestamps)
- `trading_experience` — beginner / intermediate / advanced → unlocks tutorial gating
- `preferred_assets` — `["BTCUSDT","ETHUSDT"]` → asset quick-access in trading screen
- `notification_prefs` — `{"email":true,"in_app":true,"sounds":true}`
- `last_active_at` — admin can see stale students
- `bio`, `cover_photo` — public profile for leaderboard
- Social handles — community feel

### 5.2 `trading_trades` Table — New Columns

```sql
ALTER TABLE trading_trades ADD COLUMN notes          text          NULL;
ALTER TABLE trading_trades ADD COLUMN tags           json          NULL;
ALTER TABLE trading_trades ADD COLUMN sentiment      varchar(20)   NULL;  -- 'confident','unsure','fomo'
ALTER TABLE trading_trades ADD COLUMN device_type    varchar(30)   NULL;  -- 'desktop','mobile'
```

- `notes` — student can annotate each trade post-settlement
- `tags` — `["btc-dip","news-event","FOMO"]` for self-analysis
- `sentiment` — pre-trade mood selector (teaching tool)
- `device_type` — analytics: do mobile users perform differently?

### 5.3 `trading_assets` Table — New Columns

```sql
ALTER TABLE trading_assets ADD COLUMN description    text          NULL;
ALTER TABLE trading_assets ADD COLUMN icon_url       varchar(500)  NULL;
ALTER TABLE trading_assets ADD COLUMN display_order  smallint      DEFAULT 0;
ALTER TABLE trading_assets ADD COLUMN is_featured    tinyint(1)    DEFAULT 0;
ALTER TABLE trading_assets ADD COLUMN category       varchar(50)   DEFAULT 'crypto';
ALTER TABLE trading_assets ADD COLUMN difficulty     varchar(20)   DEFAULT 'beginner';
ALTER TABLE trading_assets ADD COLUMN tags           json          NULL;
```

- `icon_url` — BTC/ETH/EUR logos in trading screen dropdown
- `display_order` — admins control which asset appears first
- `is_featured` — pinned asset in student UI
- `difficulty` — "beginner"/"intermediate"/"advanced" to guide students
- `tags` — `["crypto","volatile","popular"]`

### 5.4 `trading_wallets` Table — New Columns

```sql
ALTER TABLE trading_wallets ADD COLUMN peak_balance    bigint  DEFAULT 0;
ALTER TABLE trading_wallets ADD COLUMN total_credited  bigint  DEFAULT 0;
ALTER TABLE trading_wallets ADD COLUMN total_debited   bigint  DEFAULT 0;
ALTER TABLE trading_wallets ADD COLUMN resets_count    int     DEFAULT 0;
```

- `peak_balance` — highest balance ever reached (leaderboard stat)
- `total_credited/debited` — lifetime P&L analytics
- `resets_count` — how many times the student has reset (teaching insight)

### 5.5 New Table: `trading_achievements`

```sql
CREATE TABLE trading_achievements (
  id          bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     bigint UNSIGNED NOT NULL,
  type        varchar(80) NOT NULL,          -- 'first_trade','win_streak_3','profit_master', etc.
  title       varchar(150) NOT NULL,
  description text NULL,
  icon        varchar(100) NULL,
  meta        json NULL,
  achieved_at timestamp NOT NULL,
  created_at  timestamp NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  UNIQUE (user_id, type)                     -- each badge awarded once per user
);
```

**Achievement types to seed:**
- `first_trade` — "First Blood" — placed your first trade
- `win_streak_3` — "Hat Trick" — 3 consecutive wins
- `win_streak_5` — "On Fire" — 5 consecutive wins
- `profit_master` — "Profit Master" — total P&L > 5,000 PRACTICE$
- `btc_trader` — "BTC Hodler" — placed 10 BTC trades
- `risk_manager` — "Risk Manager" — never bet > 25% of balance
- `comeback_kid` — "Comeback Kid" — recovered from < 500 balance to > 5,000
- `century` — "Centurion" — placed 100 trades total

### 5.6 New Table: `trading_notifications`

```sql
CREATE TABLE trading_notifications (
  id          bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     bigint UNSIGNED NOT NULL,
  type        varchar(80) NOT NULL,          -- 'trade_settled','achievement_earned','balance_low'
  title       varchar(200) NOT NULL,
  body        text NULL,
  action_url  varchar(500) NULL,
  icon        varchar(100) NULL,
  data        json NULL,
  read_at     timestamp NULL,
  created_at  timestamp NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id, read_at)
);
```

### 5.7 New Table: `trading_leaderboard_snapshots`

```sql
CREATE TABLE trading_leaderboard_snapshots (
  id           bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      bigint UNSIGNED NOT NULL,
  period       varchar(20) NOT NULL,         -- 'daily','weekly','all_time'
  period_date  date NOT NULL,
  rank         int NOT NULL,
  trades_count int DEFAULT 0,
  win_rate     decimal(5,2) DEFAULT 0,
  net_pnl      bigint DEFAULT 0,
  peak_balance bigint DEFAULT 0,
  score        bigint DEFAULT 0,             -- composite score for ranking
  computed_at  timestamp NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE (user_id, period, period_date),
  INDEX (period, period_date, rank)
);
```

---

## 6. Unbuilt Planned Features

### Feature F1: Student Onboarding Flow
**What:** Multi-step registration → profile setup → tutorial → first trade
**Why:** Currently students hit a generic Laravel register form
**Screens needed:**
1. Register (name, email, password, phone)
2. Profile setup (country, timezone, trading experience)
3. Welcome tour (Driver.js guided walkthrough)
4. First trade assistant (highlighted controls, demo mode)

### Feature F2: Student Profile Page
**What:** `/trade/profile` — editable profile with avatar, stats, bio
**Shows:** Avatar, display name, country/timezone, total trades, win rate, best streak, achievements badges
**Actions:** Edit name/bio/avatar, change password, notification preferences

### Feature F3: Leaderboard
**What:** `/trade/leaderboard` — public ranking of all students
**Tabs:** This Week | This Month | All Time
**Columns:** Rank, Avatar, Name, Win Rate, Net P&L, Trades, Score
**Student privacy:** Show/hide real name toggle (replace with username)
**Admin:** Generate/refresh leaderboard via artisan command + schedule

### Feature F4: In-App Notification System
**What:** Bell icon in trading screen nav, dropdown of recent notifications
**Events that trigger:** Trade settled (win/loss), Achievement earned, Balance topped up, Balance low (< 500)
**Implementation:** `NotificationService` → inserts into `trading_notifications` table → client polls every 30s

### Feature F5: Achievement / Badge System
**What:** Automatic badge awarding when criteria are met
**Trigger:** After trade settlement via `SettleTradeJob` or dedicated `AwardAchievementsJob`
**Display:** Badge gallery on student profile, badge animation on trading screen when earned

### Feature F6: Admin Analytics Dashboard (Charts)
**What:** Replace raw number cards with ApexCharts visualizations
**Charts needed:**
- Daily trades over 30 days (area chart)
- Win/loss/tie ratio over time (stacked bar)
- Asset popularity (pie/donut)
- Student activity heatmap (calendar heatmap)
- Top students by P&L (horizontal bar)
- Platform revenue simulation (total staked vs total paid out)

### Feature F7: Full Trading History Page
**What:** `/trade/history` — full paginated history with filters
**Filters:** Date range, Asset, Direction (BUY/SELL), Status (won/lost/tie)
**Columns:** Date, Asset, Direction, Stake, Entry, Exit, P&L, Duration
**Export:** CSV download button

### Feature F8: Binance Cache Warmer Schedule
**What:** `WarmLiveCache` artisan command scheduled every 30 seconds
**File:** `app/Console/Kernel.php` (create if missing) or `routes/console.php`
```php
Schedule::command('trading:warm-cache')->everyThirtySeconds();
```
**Ensures:** Live prices are always fresh in Redis before student requests

### Feature F9: More Assets (10 total → 20)
**Additions:**
- XAUUSD (Gold/USD SIM) — commodity
- XAGUSD (Silver/USD SIM) — commodity
- GBPUSD-SIM (GBP/USD) — forex
- USDJPY-SIM (USD/JPY) — forex
- AAPL-SIM (Apple Inc SIM) — stock index simulator
- TSLA-SIM (Tesla SIM) — stock
- SPXUSD-SIM (S&P 500 SIM) — index
- OIL-SIM (Crude Oil SIM) — commodity
- BNBUSDT (Binance BNB, live) — crypto
- SOLUSDT (Solana, live) — crypto

### Feature F10: Tournament / Challenge Mode
**What:** Admin creates a timed challenge (e.g. "1-Hour BTC Sprint")
**Rules:** All students start with 5,000 PRACTICE$, trade only BTCUSDT for 1 hour
**Winner:** Highest balance at end of tournament
**New table needed:** `trading_tournaments` and `trading_tournament_participants`

### Feature F11: Trade Notes & Journal
**What:** After a trade settles, student sees a "Add Note" prompt in the right panel
**Saves to:** `trading_trades.notes`
**Journal page:** `/trade/journal` — all trades with notes, grouped by date, filterable

### Feature F12: Mobile PWA
**What:** Progressive Web App manifest + service worker
**Files:** `public/manifest.json`, `public/sw.js`
**Features:** Install prompt, offline fallback page, app icon for homescreen

---

## 7. Step-by-Step Execution Task List

### Status Legend
- ✅ **Done** — implemented, tested, committed
- 🔄 **In Progress** — actively being built
- ⬜ **Planned** — not started
- 🔴 **Blocked** — waiting on dependency
- 💀 **Deferred** — postponed

### Effort Legend: `S` ≤ 2h · `M` 2–6h · `L` 6–16h · `XL` > 16h

---

### Phase A — Foundation Hardening (Do First)

| # | Task | Status | Effort | Acceptance Criteria |
|---|------|--------|--------|---------------------|
| A1 | Fix user role labels — replace "Legal Officer/Front Desk" with trading roles | ✅ | S | `role_label` returns "Instructor", "Student", "Moderator" as appropriate |
| A2 | Schedule `WarmLiveCache` command every 30s | ✅ | S | `php artisan schedule:list` shows the command; Redis has fresh Binance prices |
| A3 | Run all DB schema enhancements (§5.1–5.7 migrations) | ✅ | M | All new columns exist; `php artisan migrate:status` clean |
| A4 | Install `spatie/laravel-activitylog` + log trade placements and wallet credits/debits | ✅ | M | Activity log table has entries after a test trade; admin can view audit trail |
| A5 | Install `barryvdh/laravel-debugbar` (dev only) | ✅ | S | Debugbar appears on admin pages in local env only |
| A6 | Install `intervention/image` — resize avatars to 200x200 on upload | ✅ | S | Uploaded avatar stored at 200×200px max |
| A7 | Download + vendor all new frontend libs (ApexCharts, Alpine.js, Day.js, canvas-confetti) | ✅ | S | Files in `public/vendor/`; no CDN calls |
| A8 | Create `CryptocoineuxSeeder` with 50 student users + wallets + 200+ trades | ✅ | M | `php artisan db:seed --class=CryptocoineuxSeeder` inserts ≥50 users, ≥200 trades |

> **Phase A — COMPLETE (8/8).** Notes: A4 trait namespaces moved in activitylog v5
> (`Models\Concerns\LogsActivity`, `Support\LogOptions`, `dontLogEmptyChanges()`);
> admin audit viewer at `/admin/trading/activity`. A6 uses GD + intervention v4 API
> (`decodePath()->cover()->encodeUsingFileExtension('jpg')`). A8 seeded 50 students /
> ~300 trades / 514 ledger entries with 0 ledger inconsistencies.

---

### Phase B — UI/UX Consistency Sprint

| # | Task | Status | Effort | Acceptance Criteria |
|---|------|--------|--------|---------------------|
| B1 | Implement design token CSS variables across ALL views | ✅ | M | Single `_tokens.css` file; all components use `var(--bg-surface)` etc. |
| B2 | Stake input redesign — % of balance, formatted display, smart quick-stakes | ✅ | M | "25% of balance" shows below input; auto-cap at max_stake with shake |
| B3 | Balance display — daily P&L below balance, animate on change | ✅ | M | Balance slides up when credited; shows "+240 today (▲2.4%)" |
| B4 | Add potential payout to BUY/SELL buttons: "BUY ▲ +80 PRACTICE$" (dynamic) | ✅ | S | Payout updates in real-time as stake input changes |
| B5 | Add loading skeleton to chart area during feed fetch | ✅ | S | Shimmer animation visible for ≥1s before candles appear |
| B6 | Add win/loss micro-animation — confetti (win), shake (loss) | ✅ | M | canvas-confetti fires on won trade; balance shakes on lost trade |
| B7 | Add asset icons/logos to dropdown and topbar symbol | ✅ | M | BTC/ETH/EUR icons show; PNG files in `public/images/assets/` |
| B8 | Open trade: add circular time-remaining progress ring | ✅ | M | SVG ring depletes over trade lifetime; turns red in last 5s |
| B9 | Admin dashboard: replace number cards with ApexCharts | ✅ | L | Trades/day area chart + win-rate donut visible and responsive |
| B10 | Mobile trading screen: fix bottom controls on viewport < 680px | ✅ | M | SELL/BUY buttons stack vertically; all controls accessible without horizontal scroll |
| B11 | Add network-offline banner to trading screen | ✅ | S | Yellow banner: "Connection lost — prices paused" when `navigator.onLine = false` |
| B12 | Add chart type toggle: Candles / Line / Area | ✅ | M | Three buttons in topbar; chart re-renders on toggle |
| B13 | Add price alert animation: green flash on up tick, red on down | ✅ | S | `#tsPrice` pulses color on every price change |

---

### Phase C — Core Missing Features

| # | Task | Status | Effort | Acceptance Criteria |
|---|------|--------|--------|---------------------|
| C1 | **Student onboarding** — dedicated registration flow with profile setup step | ✅ | L | New student completes 3-step registration; wallet auto-created; guided to trade screen |
| C2 | **Student profile page** `/trade/profile` | ✅ | L | Student can edit name, avatar, bio, timezone, experience level; stats visible |
| C3 | **Full trading history page** `/trade/history` with filters + CSV export | ✅ | L | Date/asset/status filters work; CSV download generates valid file; paginated ≥20/page |
| C4 | **In-app notification system** — bell icon, dropdown, trade/achievement alerts | ✅ | L | Bell shows unread count; clicking shows last 10 notifications; mark-all-read works |
| C5 | **Leaderboard page** `/trade/leaderboard` — weekly/monthly/all-time tabs | ✅ | L | Ranked table renders; updates after trade settlement; student rank highlighted |
| C6 | **Achievement system** — `AwardAchievementsJob`, badge gallery on profile | ✅ | L | `first_trade` badge awarded after first settled trade; badge gallery page renders |
| C7 | **Admin analytics charts** using ApexCharts | ✅ | L | Dashboard shows trades/day area, win-rate donut, asset-popularity pie — all live data |
| C8 | **Binance cache warmer** scheduled in `routes/console.php` | ✅ | S | `php artisan schedule:test` runs WarmLiveCache; Redis key `live_price:BTCUSDT` exists |
| C9 | **Trade notes & journal** — note after settlement, `/trade/journal` page | ✅ | M | Post-settlement toast has "Add Note" button; journal page filters by note/tag |
| C10 | **More assets** — add 10 new assets (Gold, Silver, GBP/USD, S&P500, etc.) | ✅ | M | 13 assets in `trading_assets`; all render in dropdown; SIM driver handles new symbols |
| C11 | **Admin: enable/disable assets inline** (no page reload) — Alpine.js toggle | ✅ | S | Toggle switch on assets table instantly enables/disables with optimistic UI |
| C12 | **Admin: queue depth indicator** on dashboard | ✅ | S | "Queue: 0 jobs" stat card on dashboard; links to Horizon (if installed) |

---

### Phase D — Advanced Features

| # | Task | Status | Effort | Acceptance Criteria |
|---|------|--------|--------|---------------------|
| D1 | **Tournament mode** — admin creates challenge, students compete | ✅ | XL | Admin can create/end tournament; students see tournament timer; winner declared |
| D2 | **Chart indicators** — MA(20), MA(50), RSI(14) toggles | ✅ | L | Three toggle buttons above chart; overlays render using LightweightCharts series |
| D3 | **spatie/laravel-permission** — replace `role` column with proper RBAC | ✅ | L | Roles: admin, instructor, student, moderator; all middleware uses permission checks |
| D4 | **Laravel Horizon** — queue monitoring at `/admin/horizon` | ✅ | M | Horizon dashboard accessible; failed jobs visible; throughput metrics shown |
| D5 | **PWA manifest + service worker** | ✅ | M | App installable on Android/iOS; offline fallback page works |
| D6 | **Dark/light mode toggle** (trading screen + admin) | ✅ | M | Preference saved in localStorage + user profile; both themes polished |
| D7 | **Laravel Telescope** (dev only) | ✅ | S | `/telescope` shows requests, queries, jobs in local env |
| D8 | **Trade history CSV export** | ✅ | S | Download button on history page generates CSV with all trade fields |
| D9 | **Sound effects** via Howler.js — win chime, lose buzz, tick sound | ✅ | M | Sounds play on events; mute toggle in nav; setting persisted in localStorage |
| D10 | **Guided onboarding tour** via Driver.js | ✅ | M | First-visit overlay highlights each UI element with description |
| D11 | **Legacy code purge** — delete LegalCase, Client, Document, Transaction controllers/views/routes | ✅ | M | No legacy routes remain; all legacy views deleted; only trading-relevant code |
| D12 | **API layer for future mobile app** — REST endpoints via Laravel Sanctum | ✅ | XL | Token-authenticated endpoints: /api/v1/trade, /api/v1/wallet, /api/v1/history |

---

### Phase E — Data & Quality

| # | Task | Status | Effort | Acceptance Criteria |
|---|------|--------|--------|---------------------|
| E1 | **Master seeder** — `CryptocoineuxSeeder` with 50 students, 200+ trades | ✅ | L | See §8 below for full spec |
| E2 | **Feature tests** for TradeController (place, settle, history) | ✅ | M | All HTTP tests pass; edge cases: insufficient balance, invalid expiry, wrong user |
| E3 | **Feature tests** for WalletService (credit, debit, consistency) | ✅ | M | Ledger consistency verified; negative balance prevented; double settlement blocked |
| E4 | **Browser/smoke test script** — curl all public routes, assert 200/302 | ✅ | S | Script outputs green for every route; no 500 errors |
| E5 | **Continuous queue worker** via MAMP startup script or launchd plist | ✅ | S | Queue worker auto-restarts after MAMP restart; no manual `queue:work` needed |

---

## 8. Dummy Data Contract

> **Rule:** No feature can be marked ✅ Done unless the corresponding seeder produces ≥ 50 realistic dummy records and `php artisan db:seed` runs cleanly.

### 8.1 Master Seeder Plan — `CryptocoineuxSeeder`

```
Class: Database\Seeders\CryptocoineuxSeeder
Triggered by: DatabaseSeeder or standalone

Produces:
├── Users (50 student accounts)
│     name: Faker::name()
│     email: unique, faker
│     password: 'password' (bcrypt, documented)
│     role: 'student'
│     country: random from 20 countries
│     timezone: matching country
│     trading_experience: 60% beginner / 30% intermediate / 10% advanced
│     preferred_assets: random 1–3 from asset list
│     created_at: spread over last 90 days
│
├── Wallets (1 per student)
│     starting balance: 10,000 PRACTICE$
│     post-trade balance: computed from trades
│
├── Trades (200+ total, 4–8 per student)
│     asset: random from enabled assets
│     direction: 50% up / 50% down
│     mode: 90% sim / 10% live
│     stake: random 50–2000
│     status: 45% won / 45% lost / 10% tie
│     entry/exit price: realistic values from SimulatedDriver
│     opened_at: spread over last 60 days
│     expiry_seconds: random from asset.allowed_expiries
│
├── WalletEntries (auto-generated by WalletService during seeding)
│
├── Achievements (random badge assignments)
│     first_trade: ALL 50 users
│     win_streak_3: 15 users
│     profit_master: 5 users
│
├── Notifications (3–5 per student = 150–250 total)
│     trade_settled: for recent trades
│     achievement_earned: matching achievement records
│
└── LeaderboardSnapshot (weekly, monthly, all_time for each user)
```

### 8.2 Seeder Per Feature

| Feature | Seeder Class | Min Records |
|---------|-------------|-------------|
| Students | `CryptocoineuxSeeder` | 50 users |
| Trades | `CryptocoineuxSeeder` | 200 trades |
| Wallet entries | auto via WalletService | 400+ entries |
| Assets | `TradingSeeder` + new assets | 13 assets |
| Achievements | `AchievementSeeder` | 75+ awards |
| Notifications | `NotificationSeeder` | 150+ notifications |
| Leaderboard | `LeaderboardSeeder` | 50 × 3 periods = 150 rows |
| Trade notes | `TradeJournalSeeder` | 100+ annotated trades |
| Tournaments | `TournamentSeeder` | 3 past tournaments + results |

### 8.3 Seeder Run Order

```php
// DatabaseSeeder.php
$this->call([
    TradingSeeder::class,       // assets + settings (idempotent)
    AdminUserSeeder::class,     // admin user ID=1
    CryptocoineuxSeeder::class, // 50 students + 200 trades
    AchievementSeeder::class,   // badges based on trade data
    NotificationSeeder::class,  // notifications based on trades
    LeaderboardSeeder::class,   // snapshots based on trade data
    TradeJournalSeeder::class,  // notes on settled trades
    TournamentSeeder::class,    // 3 sample tournaments
]);
```

**Run command:**
```bash
php artisan migrate:fresh --seed
# or to add without wiping:
php artisan db:seed --class=CryptocoineuxSeeder
```

---

## 9. Architecture Notes & Decisions

### 9.1 Role System Redesign

Replace the single `users.role` varchar column with Spatie Permissions:

```
Roles:
  admin       — full system access
  instructor  — can manage assets, view all student data
  moderator   — can view students, cannot change settings
  student     — can trade, view own profile/history

Gates to update:
  canAccessAdmin() → hasAnyRole(['admin','instructor','moderator'])
  isAdmin()        → hasRole('admin')
```

### 9.2 Service Layer Additions Needed

```
App\Services\Trading\
├── AchievementService.php    — check + award badges after settlement
├── NotificationService.php   — create in-app notifications
├── LeaderboardService.php    — compute and cache rankings
├── JournalService.php        — save/retrieve trade notes
└── TournamentService.php     — tournament lifecycle management
```

### 9.3 Event / Listener Pattern

Instead of doing everything in `SettleTradeJob`, emit events:

```php
// In SettlementService::settle()
event(new TradeSettled($trade));  // → listeners award achievements, send notifications, update leaderboard
```

```
Events:
  TradeSettled($trade)
  AchievementEarned($user, $achievement)
  BalanceCritical($user, $wallet)    -- balance < 500
  WalletToppedUp($user, $amount)
```

### 9.4 Caching Strategy

```
Redis keys:
  live_price:{symbol}           TTL 30s   — BinanceLiveDriver prices
  leaderboard:weekly:{date}     TTL 10m   — pre-computed leaderboard
  leaderboard:monthly:{date}    TTL 1h
  leaderboard:all_time          TTL 6h
  student_stats:{user_id}       TTL 5m    — profile page stats
```

---

## 10. Quick Wins (Do These First — Each < 1 Hour)

These can be done immediately to improve the experience with minimal risk:

1. **QW1** — Download ApexCharts to `public/vendor/js/apexcharts.min.js`
2. **QW2** — Download Alpine.js to `public/vendor/js/alpine.min.js`
3. **QW3** — Schedule `WarmLiveCache` command (2 lines of code in `routes/console.php`)
4. **QW4** — Change role labels: "officer" → "Instructor", "frontdesk" → "Moderator", add "student" default role
5. **QW5** — Add `% of balance` indicator below stake input in trading screen JS (10 lines)
6. **QW6** — Show potential payout on BUY/SELL buttons: update `updatePayout()` to accept stake value
7. **QW7** — Run the new DB migrations (enhancements from §5) so columns exist for upcoming features
8. **QW8** — Seed more assets (Gold, Silver, GBP/USD) using existing TradingSeeder pattern
9. **QW9** — Add unread notification count to nav bell (even if just `0` for now — structure ready)
10. **QW10** — Remove dead legacy nav routes from admin sidebar completely

---

## 11. Overall Progress Tracker

> 🎉 **ALL 50 PLANNED TASKS COMPLETE** (Phases A–E). Plus: a critical PHP-8.2/MAMP
> compatibility fix (the app was 500ing under MAMP after package installs pulled in
> a PHP ≥8.3/8.4 requirement). 95 automated tests pass. Queue runs on Redis via
> Horizon; RBAC via spatie/permission; REST API via Sanctum.

```
Phase A — Foundation Hardening    ██████████  8/8   (100%) ✅
Phase B — UI/UX Sprint            ██████████  13/13 (100%) ✅
Phase C — Core Missing Features   ██████████ 12/12  (100%) ✅
Phase D — Advanced Features       ██████████ 12/12  (100%) ✅
Phase E — Data & Quality          ██████████  5/5   (100%) ✅

Trading Engine (core)             ██████████  COMPLETE ✅
Admin Panel (basic)               ███████░░░  75% ✅
Trading Screen (UI)               ████████░░  80% ✅
Queue / Settlement                ██████████  COMPLETE ✅
Tests                             ██████████  95 passing ✅
Dummy Data                        ██████████  COMPLETE ✅
```

---

*This document is the single source of truth for Cryptocoinex development priorities.
Update status columns and progress bars as tasks are completed.
All dummy data seeded for this project is entirely synthetic — no real users, no real money.*
