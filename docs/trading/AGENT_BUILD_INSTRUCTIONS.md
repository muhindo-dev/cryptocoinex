# AGENT BUILD INSTRUCTIONS — Trading Trainer

> **Audience:** the AI coding agent that will build this project.
> **Read this entire file before doing anything.** It is your single source of truth for
> *how* to work. The *what* lives in the seven design docs in this folder.
> **Golden rule: plan first, get approval, then execute one task at a time — never code
> ahead of the plan, never skip verification.**

---

## 0. Mission in one paragraph

Convert the existing **Laravel 12 / PHP 8.2** app (`cryptocoinex`, currently a legal-case/LMS
system) into a **trading learning simulator** for students: an ExpertOption-style up/down
fixed-time trading trainer with a live candlestick chart, a user toggle between a **simulated
price engine** and **live Binance crypto data**, server-settled trades, and a **virtual
(fake-money) double-entry wallet**. It is **purely educational** — no real money, ever. You
will build it **as a module inside the existing app**, reusing its admin shell, auth, and
assets, with a **jQuery + AJAX frontend** and **TradingView Lightweight Charts**.

---

## 1. Required reading (in order, before planning)

You MUST read all of these before writing any plan or code. Do not assume — read them.

1. `README.md` — index + one-paragraph summary.
2. `00_PROJECT_OVERVIEW.md` — vision, scope, audience, MVP success criteria.
3. `01_DECISIONS.md` — every locked decision (D1–D10). **These are settled. Do not relitigate
   them** unless the human explicitly reopens one.
4. `02_ARCHITECTURE.md` — system design, the **data-source seam**, layering rules, coding /
   security / performance / testing guidelines. This governs *how you write code*.
5. `03_FEATURES.md` — feature list + the **MVP checklist** (your scope boundary).
6. `04_DATA_AND_ENGINE.md` — simulation math (GBM), Binance integration, the feed contract.
7. `05_DATA_MODEL.md` — exact tables, columns, models, migrations, and **invariants**.
8. `06_MIGRATION_AND_ROADMAP.md` — the conversion tasks (T0–T13) and **phases (0–6)** you
   will execute, plus the open questions and risks.

After reading, you should be able to restate the architecture and the phase plan in your own
words. If anything in the docs is ambiguous or contradictory, **stop and ask the human** —
do not guess.

---

## 2. Non-negotiable ground rules

1. **Fake money only.** Never add deposits, withdrawals, payment rails, or anything that
   moves real value. This is an educational simulator (Decision D1).
2. **Reuse, don't rewrite.** Build inside the existing Laravel app. Keep the admin shell,
   auth, layouts, and assets (Decision D2). New code goes under `*\Trading\*` namespaces.
3. **Never delete legacy code in early phases.** Hide the legal/LMS screens from navigation;
   leave their tables/models dormant. Deletion, if ever, is a separate late cleanup pass.
4. **Work on a branch.** All work on `feature/trading-trainer`. Never commit straight to main.
5. **Server is authoritative.** The client never decides prices, outcomes, or balances.
   Entry/exit prices and settlement are computed server-side (Decisions D8, D9).
6. **Money is integers.** Virtual currency amounts are integers; prices are decimals. Every
   wallet change goes through `WalletService` inside a DB transaction with a ledger entry.
7. **Honour the data-source seam.** Nothing outside the MarketData drivers may know whether
   data is simulated or live. The frontend is mode-agnostic.
8. **Test before you call anything done.** No task is complete with failing tests, partial
   implementation, or unverified behaviour (see §6).
9. **Stay in scope.** Build the MVP checklist first. V2/V3 features wait for their phase.
   If you think something is missing from scope, propose it — don't silently add it.
10. **Decide autonomously.** You are empowered to make your own decisions. When a choice
    isn't covered by the docs, pick the most sensible default (see §3a for the recommended
    defaults), **document the decision and your reasoning in a `DECISIONS_LOG.md`**, and keep
    moving. Do **not** stop to ask the human for routine choices. Only pause for a human if a
    decision would (a) break a non-negotiable ground rule, (b) introduce real money, or
    (c) require throwing away large amounts of completed work to reverse. Everything else:
    decide, log, proceed.

---

## 3. Operating protocol — how you work every session

You follow this loop. Do not deviate.

### Step 1 — Orient
- Re-read this file and skim the relevant design doc for the current phase.
- Run `git status` / check the branch. Confirm you're on `feature/trading-trainer`.
- Review the existing task list (if any) to see what's done and what's next.

### Step 2 — Plan (before any code)
- Produce a **written task list** for the current phase, broken into small, verifiable tasks.
- Each task must have: a clear outcome, the files it touches, and how it will be verified.
- Order tasks by dependency. Map them to the roadmap (T0–T13 / Phases 0–6).
- **You own the plan. Approve it yourself and proceed** — you do not need to wait for the
  human. Post the plan so it's visible, then start executing. Adjust the plan as you learn,
  recording any notable changes in `DECISIONS_LOG.md`.

### Step 3a — Recommended default decisions (use these unless you find a clear reason not to)
You are pre-authorised to proceed with these defaults for the open questions in
`06_MIGRATION_AND_ROADMAP.md` §6. Log any deviation.

| Question | Default decision to take |
|----------|--------------------------|
| Legacy legal/LMS features | **Hide** from navigation, leave tables/models dormant. Do **not** delete in early phases. |
| Starting practice balance | **10,000** virtual units (label "PRACTICE$"), configurable in settings. |
| Cache backend | Use **Redis if available**, otherwise fall back to Laravel's **database/file cache**. Detect and pick automatically. |
| First seeded assets | **BTCUSDT** and **ETHUSDT** (live-capable) + one simulated forex (**EURUSD-SIM**). |
| Branding | **Keep the `cryptocoinex` name and existing logos** for now; make app name a config value so it's easy to change later. |
| Anything else undocumented | Pick the simplest, most reversible option that respects the ground rules; log it. |

### Step 3 — Execute one task at a time
- Mark the task **in progress**. Work only on that task.
- Make the smallest correct change set. Follow the coding guidelines in `02_ARCHITECTURE.md`.
- Do **not** batch unrelated changes or jump ahead to later tasks.

### Step 4 — Verify the task
- Write/extend tests for the task's logic (see §6). Run them.
- Run `php artisan test` and `./vendor/bin/pint`. Both must pass.
- For UI tasks, describe the manual check performed (and, where possible, take a screenshot).
- Confirm relevant invariants still hold (`05_DATA_MODEL.md` §5).

### Step 5 — Report & checkpoint
- Mark the task **complete**. Give the human a one- or two-line summary of what changed.
- Commit with a clear message (Conventional Commits style, e.g. `feat(wallet): add ledger debit`).
- Move to the next task. At the end of each **phase**, stop at the phase **gate** and get a
  green light before starting the next phase.

> **Cadence:** "as we tres" — task by task, small steps, verify each, checkpoint often. Never
> a giant unreviewed dump of code.

---

## 4. First things first — project setup (do this before Phase 1 code)

Before building features, get the environment healthy and confirm reuse points.

```
S1. Confirm the app runs: composer install; copy .env; php artisan key:generate; migrate;
    npm install; npm run build; php artisan serve — verify the admin login works today.
S2. Create and switch to branch: git checkout -b feature/trading-trainer.
S3. Confirm queue + cache: ensure `php artisan queue:work` runs; decide cache backend
    (ask human: Redis vs file/database — see open questions).
S4. Inventory reuse points: admin layout Blade file(s), the `admin` middleware, the asset
    pipeline (vite/tailwind), shared components — note their paths for reuse.
S5. Resolve the 5 open questions in 06_MIGRATION_AND_ROADMAP.md §6 **yourself** using the
    default-decisions table in §3a. Record your choices in `DECISIONS_LOG.md`. Do not wait
    for the human.
S6. Add a top-level note/changelog entry that the project is being repurposed.
```

Only once setup is verified do you begin Phase 1. **You proceed on your own decisions** — no
approval handshake is required between phases; just keep `DECISIONS_LOG.md` current.

---

## 5. Execution map — phases → what you build

Follow `06_MIGRATION_AND_ROADMAP.md`. Summary of the order you build in:

| Phase | You build | Gate (must pass to proceed) |
|-------|-----------|------------------------------|
| **0 Setup** | §4 above | App runs, branch created, questions answered |
| **1 Foundation** | Shell rebrand + nav cleanup; `trading_*` migrations/models/seeder; `WalletService` + tests | Wallet invariants tested & green |
| **2 Sim MVP** | Driver interface + `SimulatedDriver` (GBM); feed endpoints; chart + `trade.js`; toggle (UI); `TradeService` + `SettleTradeJob` + `SettlementService`; trade UI | Place→settle works end-to-end on sim; settlement fully unit-tested |
| **3 Live data** | `BinanceLiveDriver` + cache warmer; live toggle for crypto | Live feed cached, resilient, falls back to sim |
| **4 Admin** | Asset CRUD, student/wallet admin, settings & flags (reuse admin shell) | Instructor can run it without code |
| **5 Learning + polish** | Disclaimers, stats, lessons/tooltips, scenarios, responsive pass | MVP checklist fully ticked |
| **6 Stretch** | Indicators, leaderboards, stocks/forex live, WebSocket push | Per-feature acceptance criteria |

**The MVP = Phases 1–2 (+ key Phase 4 admin bits).** Get a usable simulated trainer working
before anything fancy.

---

## 6. Definition of Done & verification (apply to every task and phase)

A unit of work is **done** only when ALL hold:

1. It meets the acceptance criteria in `03_FEATURES.md` for that feature.
2. New business logic has **unit/feature tests**, and `php artisan test` is **green**.
   - Exhaustively test settlement (UP/DOWN × up/down/equal price).
   - Test wallet: non-negative balance, correct debit/credit, `sum(ledger) == balance`,
     idempotent settlement (no double credit).
   - Mock `BinanceLiveDriver` — never hit the network in tests.
3. `./vendor/bin/pint` passes (PSR-12).
4. Data-model invariants (`05_DATA_MODEL.md` §5) verified.
5. Manual QA of the affected trade loop performed (note what you checked).
6. Code committed with a clear message; human informed.

**For high-stakes logic (settlement, wallet), do an extra adversarial review pass** — try to
find a way to double-credit, overdraw, or settle twice, and prove it can't happen.

---

## 7. Coding conventions (quick reference — full detail in 02_ARCHITECTURE.md)

- Namespaces: `App\Models\Trading\*`, `App\Services\Trading\*`,
  `App\Http\Controllers\Trading\*`, `App\Http\Controllers\Admin\Trading\*`,
  `App\Jobs\Trading\*`, drivers under `App\Services\Trading\Drivers\*`.
- Routes: student under prefix `/trade` (name `trade.`, `auth` middleware); admin under the
  existing `/admin` group with `admin` middleware and the existing layout.
- Controllers thin; services hold logic; drivers hold data acquisition; jobs hold deferred work.
- Validate every input with Form Requests against admin-configured limits.
- Cache live data; never call Binance from the student request path.
- Frontend: one modular `trade.js` (`initChart`, `startFeed`, `placeTrade`, `pollTrade`).
  Include the required **TradingView attribution link** on chart pages (license).
- Feature-flag live mode so it can be disabled globally with sim fallback.

---

## 8. Communication protocol with the human

- **Before each phase:** post your task plan for visibility, then proceed — no approval wait.
- **During a phase:** after each task, a one/two-line progress note + commit. Keep the task
  list updated (in progress / done).
- **At each phase gate:** post a short summary of what works + test results, then continue to
  the next phase on your own judgement.
- **Decisions:** make them yourself and log them in `DECISIONS_LOG.md`. Only escalate to the
  human for the three exceptions in ground rule #10 (broken ground rule / real money /
  large irreversible rework).
- **Keep it concise and direct.** No walls of text; show working software and green tests.

---

## 9. Guardrails — things that must never happen

- ❌ Real money, payments, deposits, withdrawals, or anything implying real value.
- ❌ Client-side computation of outcomes, prices, or balances.
- ❌ A wallet balance going negative, or a trade settling more than once.
- ❌ Floats for virtual-currency amounts.
- ❌ Mode (sim/live) leaking outside the driver seam.
- ❌ Deleting legacy data/models in early phases.
- ❌ Hitting Binance directly from a student request, or shipping without caching.
- ❌ Marking a task done with failing/absent tests.
- ❌ Building V2/V3 features before the MVP is complete.
- ❌ Committing to main.
- ❌ Making an undocumented decision without recording it in `DECISIONS_LOG.md`.

---

## 10. Your very first actions (start here)

1. Read all docs in §1.
2. Resolve the 5 open questions (`06_MIGRATION_AND_ROADMAP.md` §6) **yourself** using the
   defaults in §3a; create `DECISIONS_LOG.md` and record them.
3. Run the Phase 0 setup checklist (§4) and report the app's current health.
4. Produce the **Phase 1 task list** (small, verifiable, dependency-ordered), post it, and
   **begin** — you approve your own plan.
5. Execute Phase 1 **one task at a time**, verifying each (§6), checkpointing as you go, then
   continue through the phases on your own judgement.

You are trusted to make decisions and drive this to completion. Build carefully, test
relentlessly, log your decisions, and move one task at a time. Good luck.
