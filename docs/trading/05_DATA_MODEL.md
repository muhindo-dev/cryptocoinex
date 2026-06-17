# Data Model

> Database schema for the trading module. New tables only; existing legacy tables are left
> intact. All new models live under `App\Models\Trading\`.

> **Money rule:** virtual currency amounts are stored as **integers** (smallest unit) to
> avoid float drift. Prices are stored as `DECIMAL` for display/calc precision.

---

## 1. Entity overview

```
User (existing)
  └─1:1─ Wallet
            └─1:many─ WalletEntry        (ledger: holds, payouts, top-ups)
  └─1:many─ Trade ──many:1── Asset
                  └─ references WalletEntry (stake hold + payout)

Asset (admin-configured)
  ├─ simulation params (seed, drift, volatility, start price)
  └─ live mapping (binance symbol)

TradingSetting (singleton-ish key/value or single row) — global config
```

---

## 2. Tables

### 2.1 `trading_assets`
Admin-configured tradable instruments.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| symbol | string, unique | e.g. `BTCUSDT`, `EURUSD-SIM` |
| name | string | Display name, e.g. "Bitcoin / USDT" |
| asset_class | enum | `crypto` \| `forex` \| `stock` \| `sim` |
| payout_percent | decimal(5,2) | e.g. 80.00 = 80% payout on win |
| min_stake | unsigned int | virtual units |
| max_stake | unsigned int | virtual units |
| allowed_expiries | json | seconds list, e.g. `[30,60,300]` |
| supports_live | bool | can this asset use live mode? |
| live_symbol | string, nullable | upstream symbol (Binance), if live |
| sim_start_price | decimal(18,8) | engine S0 |
| sim_drift | decimal(8,5) | engine μ |
| sim_volatility | decimal(8,5) | engine σ |
| sim_seed | bigint | deterministic seed |
| enabled | bool | shown to students? |
| timestamps | | |

### 2.2 `trading_wallets`
One per user.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | bigint FK → users | unique |
| balance | bigint | virtual units; **derived/maintained from ledger**, never edited directly |
| currency_label | string | display only, e.g. "PRACTICE$" |
| timestamps | | |

> `balance` is a cached convenience column kept in sync inside the same DB transaction as the
> ledger entry. Source of truth = sum of `wallet_entries`.

### 2.3 `trading_wallet_entries`
Append-only ledger. **Never updated or deleted.**

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| wallet_id | bigint FK | |
| trade_id | bigint FK, nullable | linked trade if applicable |
| type | enum | `stake_hold` \| `payout` \| `refund` \| `topup` \| `reset` \| `adjustment` |
| amount | bigint | signed: debit negative, credit positive |
| balance_after | bigint | running balance snapshot |
| meta | json, nullable | reason, admin id, etc. |
| created_at | timestamp | (no updated_at — immutable) |

Index: `(wallet_id, created_at)`.

### 2.4 `trading_trades`
One row per placed trade.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | bigint FK | |
| asset_id | bigint FK | |
| mode | enum | `sim` \| `live` — drives which driver settles it |
| direction | enum | `up` \| `down` |
| stake | bigint | virtual units |
| payout_percent | decimal(5,2) | snapshot of asset payout at open time |
| entry_price | decimal(18,8) | locked at open |
| exit_price | decimal(18,8), nullable | set at settlement |
| opened_at | timestamp | |
| expires_at | timestamp | opened_at + expiry |
| settled_at | timestamp, nullable | |
| expiry_seconds | int | chosen expiry |
| status | enum | `open` \| `won` \| `lost` \| `tie` \| `void` |
| payout_amount | bigint, nullable | credited on win/refund |
| timestamps | | |

Indexes: `(user_id, status)`, `(status, expires_at)` (for settlement sweeps), `asset_id`.

### 2.5 `trading_settings`
Global config (single row, or key/value). Examples:

| Key | Example | Meaning |
|-----|---------|---------|
| default_start_balance | 10000 | granted on registration |
| default_mode | `sim` | initial toggle state |
| live_mode_enabled | true | global kill-switch for live data |
| tie_policy | `refund` | `refund` \| `loss` |
| allow_student_reset | true | can students reset balance |

---

## 3. Models & relationships (`App\Models\Trading\`)

```php
Asset      hasMany Trade
Wallet     belongsTo User; hasMany WalletEntry
WalletEntry belongsTo Wallet, belongsTo Trade(nullable)
Trade      belongsTo User, belongsTo Asset; hasMany WalletEntry
User (existing)  hasOne Wallet; hasMany Trade   // add these relations to existing User model
```

---

## 4. Migrations (creation order)

```
1. create_trading_settings_table
2. create_trading_assets_table
3. create_trading_wallets_table
4. create_trading_wallet_entries_table   (FK -> wallets)
5. create_trading_trades_table           (FK -> users, assets)
6. add FK on wallet_entries.trade_id -> trades  (separate step to avoid circular FK)
```

All migration filenames follow the existing `YYYY_MM_DD_HHMMSS_*` convention and live in
`database/migrations/`. Provide a `TradingSeeder` that inserts a few demo assets (BTCUSDT,
ETHUSDT live-capable; one `*-SIM` forex/stock) and default settings.

---

## 5. Invariants (enforce in code + tests)

1. `wallet.balance == SUM(wallet_entries.amount)` for that wallet — always.
2. Balance **never negative**: a `stake_hold` that would overdraw is rejected before insert.
3. A trade transitions `open → {won|lost|tie|void}` **exactly once** (idempotent settlement).
4. `payout_amount` is set **iff** status ∈ {won, refund/tie}.
5. Every wallet mutation creates a ledger entry inside one DB transaction with the balance update.
6. A trade's `mode` at open == the driver used at settlement.
