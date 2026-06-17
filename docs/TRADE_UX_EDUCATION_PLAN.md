# `/trade` UX Overhaul + Education Module — Implementation Plan

> Scope: **only** the student trading experience at `/trade` and its backend.
> Goal: an ExpertOption-grade trading screen (polished, lively, error-free) and a
> full Education module (categories + 40+ hand-written articles with public
> YouTube videos). All money is virtual PRACTICE$.

---

## 0. Reference — what we borrow from ExpertOption (from the screenshots)

| Area | ExpertOption pattern | Our adaptation |
|------|----------------------|----------------|
| Left rail | Icon + label stack (Trade, Finances, Profile, Apps, Education, Help, Battles, Invite). Collapsible. | Keep our rail but add **labels under icons**, crisp active state, collapse toggle. Map to our real pages. |
| Top bar | Deposit chip · Balance pill (REAL/DEMO segmented) · "Finances" · Trophy (tournaments) | Balance pill with **DEMO** segment, deposit→reset wallet, trophy→tournaments. |
| Asset selector | Centered pill dropdown with asset icon | Keep centered; refine to a pill with monogram + chevron. |
| Right rail | Deals · Trends · Social · layout · chat · mute · fullscreen · Start Tips | Deals(panel) · History · Leaderboard(Social) · mute · fullscreen · Tour. |
| Chart | Smooth **area** chart, price pill on axis, position markers (avatar + payout), entry line | Default area, animated price pill, entry markers, polished colors. |
| Bottom bar | `– $1 +` investment · big **SELL**(red ✔) / **BUY**(green ▲) with payout% · auto-close timer | Refine to small-radius premium buttons, payout + profit, circular timer. |
| Settings panel | Language · Theme · Sounds · Trading settings · Active sessions | Theme + Sounds (we have) in a slide-over. |
| Education | Recommended carousel · category cards · article list with **Beginner/Base** level chips · article detail with **video player** + numbered sections | Build full module (below). |

**Design language:** dark, low-contrast surfaces, **Inter** font, 8–10px radii, small
buttons (28–36px), generous whitespace, subtle borders (`#1c2a3a`), gold accent
`#f59e0b`, green `#00c97b`, red `#f53b57`. Micro-interactions everywhere.

---

## 1. Font & Design Polish (foundation)

- **Vendor Inter locally** (`public/vendor/fonts/inter.woff2`, variable) — zero CDN.
  `@font-face` in `tokens.css`; set `--font-sans: 'Inter', …`. Apply on `/trade` + trade-app shell.
- Tighten the existing token scale; add `--shadow-sm/md`, `--ring` focus styles.
- Buttons: a reusable `.btn` system (sizes sm/md, variants ghost/solid/gold/buy/sell),
  active scale, focus ring, disabled state.

---

## 2. Trading Screen (`resources/views/trading/index.blade.php`) — refinements

The screen already has: asset dropdown, area/line/candle toggle, MA/RSI, stake %,
payout, confetti/shake, time-ring, offline banner, sounds, tour, theme. Polish pass:

1. **Typography**: switch to Inter; tabular-nums on all numbers; refine sizes.
2. **Left rail**: labels under each icon (Trade, Wallet, History, Journal, Ranks,
   Profile, **Education**, Tournaments, Admin), active pill, hover slide.
3. **Top bar**:
   - Balance pill → segmented **DEMO** badge + amount + currency; click = wallet.
   - Add **deposit/reset** chip (left of balance) and **tournaments trophy** (right).
   - Asset pill: monogram + symbol + 24h-style chip; smoother dropdown with categories.
4. **Chart**: default **area**; animate the price pill; polish entry line + markers;
   crosshair tooltip; smooth color transitions; keep MA/RSI.
5. **Bottom bar**: premium small buttons; investment stepper with hold-to-repeat;
   BUY/SELL show payout% + projected profit; circular auto-close timer with ring.
6. **Liveliness**: pulsing live dot, price flash, balance pop, button ripples,
   skeleton on load, toast polish, empty-state polish.
7. **Robustness / "no room for error"**:
   - Guard every DOM lookup; feature-detect libs; try/catch around chart ops.
   - Debounce stake input; clamp to min/max; never NaN.
   - Reconnect feed on focus/online; abort stale fetches (AbortController).
   - Defensive JSON parsing; graceful asset-less and feed-error states.

No backend change needed for the chart (FeedController already returns candles/price).

---

## 3. Education Module — backend

### 3.1 Tables (migrations)
```
education_categories
  id, name, slug(unique), tagline, icon(fa), accent(hex), sort_order, created_at
education_articles
  id, category_id(fk), title, slug(unique), level enum[beginner,base,advanced],
  excerpt(text), body(longtext, structured HTML/markdown-ish sections),
  youtube_id(nullable), video_title, duration(varchar e.g. '01:19'),
  thumbnail(nullable), read_minutes(int), is_recommended(bool), sort_order
education_progress            (lightweight completion tracking)
  id, user_id(fk), article_id(fk), completed_at; unique(user_id, article_id)
```

### 3.2 Models
- `EducationCategory` (hasMany articles, ordered).
- `EducationArticle` (belongsTo category; `youtubeEmbedUrl()`, `thumbnailUrl()` →
  `https://img.youtube.com/vi/{id}/hqdefault.jpg` fallback).
- `EducationProgress`.

### 3.3 Controller + routes (`/trade/education`)
- `EducationController@index` — categories, recommended (carousel), latest, with
  optional `?category=slug` + `?level=` filter, and the viewer's completed set.
- `EducationController@show` (`/trade/education/{article:slug}`) — article + video +
  sections + "next in category"; marks progress (POST) and shows related.
- `POST /trade/education/{article}/complete` — toggle completion (JSON).

### 3.4 Seeder — `EducationSeeder` (the hand-made content)
6 categories, **42 articles**, each with: real-topic title, level, 1–2 paragraph
excerpt, a structured body (intro + 4–6 numbered sections + a "Key takeaways"
list), a **public YouTube video id**, duration, read time, recommended flag.

> YouTube IDs are curated from large, long-standing finance/education channels and
> are embedded by id. They are easy to swap in the seeder if any becomes
> unavailable; the article is valuable on its own (rich written content +
> thumbnail fallback). Categories & counts:

| Category | Slug | Articles |
|----------|------|----------|
| How to Trade | how-to-trade | 8 |
| First Steps | first-steps | 6 |
| Market Analysis | market-analysis | 8 |
| Indicators & Tools | indicators-tools | 8 |
| Trading Strategies | strategies | 7 |
| Risk & Psychology | risk-psychology | 5 |
| **Total** | | **42** |

Wire `EducationSeeder` into `DatabaseSeeder`.

---

## 4. Education Module — UI (in the `trade-app` shell)

Match the ExpertOption education panel, full-page (not modal) for our web app:

- **Index** (`trading/education/index.blade.php`):
  - Hero "Trading Course" header with progress bar (x/42 completed).
  - **Recommended** horizontal carousel (cards with thumbnail + title + level).
  - **Category** tabs/pills; selecting filters the article grid.
  - Article cards: thumbnail (YouTube hqdefault), title, level chip
    (green=Beginner, blue=Base, amber=Advanced), duration, ✓ if completed.
- **Article** (`trading/education/show.blade.php`):
  - Back link, title, level + duration meta.
  - **Lazy YouTube player** (click thumbnail → iframe; no autoplay; privacy
    `youtube-nocookie.com`).
  - Excerpt, then numbered sections (rendered from body), "Key takeaways".
  - "Mark as complete" button (POST, optimistic), "Next lesson" + related.
- Add **Education** to the left rail (trading screen) and trade-app nav.

---

## 5. Quality gates

- Feature tests: education index/show render, complete toggle, guest blocked.
- Seeder runs cleanly; ≥42 articles; every article has a category + body.
- `php artisan test` green; MAMP `/trade` + `/trade/education` render 200.
- Pint clean.

---

## 6. Execution order (commits)

1. Plan (this file) + vendor Inter font + button/token polish.
2. Education backend (migrations, models, controller, routes).
3. `EducationSeeder` — 42 hand-written articles (the big content push).
4. Education UI (index + article) + nav links.
5. Trading-screen UX overhaul (typography, rails, top bar, chart, bottom bar, liveliness, robustness).
6. Tests + live verification + polish.
