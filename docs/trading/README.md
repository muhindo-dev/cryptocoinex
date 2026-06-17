# Trading Trainer — Documentation

Planning & design docs for converting `cryptocoinex` into a **trading learning simulator**
(ExpertOption-style up/down trading, fake money only, for students).

> **Status:** Design phase. No application code written yet — these docs are the blueprint
> to approve before building.

## Read in this order

1. **[00_PROJECT_OVERVIEW.md](00_PROJECT_OVERVIEW.md)** — vision, scope, audience, success criteria.
2. **[01_DECISIONS.md](01_DECISIONS.md)** — every design decision, locked, with rationale.
3. **[02_ARCHITECTURE.md](02_ARCHITECTURE.md)** — system design, the data-source seam, engineering guidelines.
4. **[03_FEATURES.md](03_FEATURES.md)** — core features + MVP checklist.
5. **[04_DATA_AND_ENGINE.md](04_DATA_AND_ENGINE.md)** — simulation engine math, Binance live integration, the toggle.
6. **[05_DATA_MODEL.md](05_DATA_MODEL.md)** — database schema, models, migrations.
7. **[06_MIGRATION_AND_ROADMAP.md](06_MIGRATION_AND_ROADMAP.md)** — conversion tasks + phased build plan.

### For the AI builder
- **[AGENT_BUILD_INSTRUCTIONS.md](AGENT_BUILD_INSTRUCTIONS.md)** — the master prompt handed to
  the AI coding agent: mission, ground rules, the plan-first → task-by-task → verify operating
  protocol, setup steps, and phase execution map. **Give the agent this file.**

## The decisions in one breath

Build the trainer as a **module inside the existing Laravel 12 app** (reusing admin + auth +
assets), with a **jQuery/AJAX frontend** and **TradingView Lightweight Charts**. Drive the
chart from a **data-source seam** that swaps between a **simulated GBM price engine** (default)
and **live Binance crypto data** via a user **toggle**. Trades are **server-settled** by
**queued jobs** against a **virtual double-entry wallet**. **Fake money only — purely
educational.** Start with **AJAX polling**; WebSockets are a later drop-in.

## Next step

Review the docs, then answer the five **open questions** at the end of
[06_MIGRATION_AND_ROADMAP.md](06_MIGRATION_AND_ROADMAP.md) so Phase 1 can begin.
