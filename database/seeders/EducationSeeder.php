<?php

namespace Database\Seeders;

use App\Models\Education\EducationArticle;
use App\Models\Education\EducationCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Hand-authored trading course: 6 tracks, 42 lessons. Each lesson pairs original
 * written content with a real, public YouTube video (validated by id). All
 * content is educational; the simulator uses virtual USD only.
 */
class EducationSeeder extends Seeder
{
    public function run(): void
    {
        EducationArticle::query()->delete();
        EducationCategory::query()->delete();

        foreach ($this->categories() as $catOrder => $cat) {
            $category = EducationCategory::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'tagline' => $cat['tagline'],
                'icon' => $cat['icon'],
                'accent' => $cat['accent'],
                'sort_order' => $catOrder,
            ]);

            foreach ($cat['articles'] as $i => $a) {
                EducationArticle::create([
                    'category_id' => $category->id,
                    'title' => $a[0],
                    'slug' => Str::slug($a[0]),
                    'level' => $a[1],
                    'duration' => $a[2],
                    'read_minutes' => $a[3],
                    'is_recommended' => $a[4],
                    'youtube_id' => $a[5],
                    'video_title' => $a[0],
                    'excerpt' => $a[6],
                    'body' => $a[7],
                    'sort_order' => $i,
                ]);
            }
        }

        $this->command->info('EducationSeeder: '.EducationCategory::count().' tracks, '.EducationArticle::count().' lessons.');
    }

    private function categories(): array
    {
        return [
            [
                'name' => 'How to Trade', 'icon' => 'fa-rocket', 'accent' => '#00c97b',
                'tagline' => 'The absolute basics — what trading is and how it works.',
                'articles' => $this->howToTrade(),
            ],
            [
                'name' => 'First Steps', 'icon' => 'fa-shoe-prints', 'accent' => '#3b82f6',
                'tagline' => 'Accounts, markets, and getting set up the right way.',
                'articles' => $this->firstSteps(),
            ],
            [
                'name' => 'Market Analysis', 'icon' => 'fa-chart-line', 'accent' => '#f59e0b',
                'tagline' => 'Reading charts, candlesticks, trends and price action.',
                'articles' => $this->marketAnalysis(),
            ],
            [
                'name' => 'Indicators & Tools', 'icon' => 'fa-sliders', 'accent' => '#8b5cf6',
                'tagline' => 'RSI, MACD, moving averages, Bollinger Bands and more.',
                'articles' => $this->indicators(),
            ],
            [
                'name' => 'Trading Strategies', 'icon' => 'fa-chess-knight', 'accent' => '#ec4899',
                'tagline' => 'Repeatable setups: support/resistance, breakouts, trends.',
                'articles' => $this->strategies(),
            ],
            [
                'name' => 'Risk & Psychology', 'icon' => 'fa-shield-halved', 'accent' => '#ef4444',
                'tagline' => 'Protect your capital and master your emotions.',
                'articles' => $this->riskPsychology(),
            ],
        ];
    }

    // ── Helpers to keep bodies readable ──────────────────────────────────────

    private function keyTakeaways(array $points): string
    {
        return "## Key takeaways\n".implode("\n", array_map(fn ($p) => "- $p", $points));
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRACK 1 — HOW TO TRADE
    // ════════════════════════════════════════════════════════════════════════
    private function howToTrade(): array
    {
        return [
            ['What Is Trading? A Plain-English Introduction', 'beginner', '17:02', 6, true, '8LRQIDAzyv8',
                'Trading is simply the act of buying and selling an asset to profit from price changes. This lesson strips away the jargon and shows you the core idea behind every trade you will ever place.',
                "## The one idea behind every trade\nAt its heart, trading is a prediction. You believe the price of something — a currency pair, a crypto coin, an index — will move in a particular direction within a window of time. If you are right, you profit. If you are wrong, you lose what you risked. Everything else is detail.\n\nIn this simulator you are predicting whether the price will be higher (BUY) or lower (SELL) when your trade expires. Nothing is bought or stored — you are purely forecasting direction.\n\n## Why prices move\nPrices move because buyers and sellers disagree about value. When more money wants to buy than sell, price rises; when more wants to sell, price falls. News, emotion, and big institutions all feed into this constant tug-of-war.\n\n## What you are actually risking\nEvery trade has a stake — the amount you commit. You decide it before entering. A disciplined trader never risks more than a small slice of their balance on any single prediction, because being wrong is a normal, unavoidable part of the game.\n\n## Demo first, always\nThe smartest beginners spend weeks on a demo balance before risking anything real. You have 10,000 PRACTICE\$ here for exactly that reason: to make every beginner mistake for free.\n\n".$this->keyTakeaways([
                    'A trade is a timed prediction about price direction.',
                    'You profit when right and lose your stake when wrong.',
                    'Risk only a small slice of your balance per trade.',
                    'Master the mechanics on demo before going live.',
                ])],

            ['How a Single Trade Works, Step by Step', 'beginner', '12:40', 5, true, 'HN6frMMfjzE',
                'From choosing an asset to seeing the result — walk through the exact lifecycle of one trade so nothing on the screen feels mysterious.',
                "## Step 1 — Pick an asset\nUse the asset selector at the top to choose what you want to trade. Beginners should start with one liquid, familiar market and stick to it until it feels natural.\n\n## Step 2 — Set your stake\nUse the amount box (or the % of balance buttons) to choose how much to risk. The screen instantly shows your potential profit so there are no surprises.\n\n## Step 3 — Choose an expiry\nThe expiry is how long until your prediction is judged. Short expiries are fast and noisy; longer ones give your idea room to breathe.\n\n## Step 4 — Predict direction\nTap BUY if you think price will rise, SELL if you think it will fall. Your entry price is locked the instant you click.\n\n## Step 5 — Wait and settle\nA countdown ring shows time remaining. When it expires, the exit price is compared to your entry, and your balance updates automatically.\n\n".$this->keyTakeaways([
                    'Asset → stake → expiry → direction → settle.',
                    'Entry price locks the moment you click.',
                    'The result is decided purely by exit vs. entry price.',
                ])],

            ['BUY vs SELL: Predicting Up and Down', 'beginner', '10:15', 4, false, 'noHsKFy7NgY',
                'BUY and SELL are the only two buttons that matter. Understand exactly what each one means and when traders reach for them.',
                "## BUY means \"up\"\nWhen you press BUY you are saying: I expect the exit price to be higher than my entry. Traders BUY when momentum, trend, or a bounce off support suggests strength.\n\n## SELL means \"down\"\nSELL is the mirror image: you expect the exit price to be lower than entry. Traders SELL into weakness, rejections from resistance, or downtrends.\n\n## There is no \"better\" button\nMarkets fall as often as they rise. Skilled traders are equally comfortable pressing SELL — refusing to short is like playing a game with half the board removed.\n\n## Direction is a decision, not a hope\nGood direction calls come from a reason: a trend, a level, a pattern. If your only reason is \"it feels like it should go up,\" you are gambling, not trading.\n\n".$this->keyTakeaways([
                    'BUY = predict higher; SELL = predict lower.',
                    'Down moves are just as tradable as up moves.',
                    'Always have a concrete reason for your direction.',
                ])],

            ['Understanding Payout and Profit', 'beginner', '09:30', 4, false, 'uh5fCPGwByM',
                'The payout percentage decides how much you make when you win. Learn how it is calculated and why it shapes every decision.',
                "## What the payout % means\nEach asset shows a payout, e.g. 80%. Win a 100 PRACTICE\$ trade at 80% and you receive your 100 back plus 80 profit — a 180 return. Lose, and you forfeit the 100 stake.\n\n## The math you must internalise\nBecause a loss costs 100% of the stake but a win pays less than 100%, you cannot win exactly half your trades and break even. At an 80% payout you need to win about 56% of the time just to stay flat.\n\n## Why this changes everything\nThis simple asymmetry is why edge and discipline matter so much. Random guessing slowly bleeds your balance. Only a genuine, repeatable edge overcomes the payout gap.\n\n## Choosing assets by payout\nHigher payouts mean you need a lower win-rate to profit. All else equal, prefer assets with stronger payouts — but never trade a market you do not understand just because the payout is high.\n\n".$this->keyTakeaways([
                    'Win pays stake + (stake × payout%); loss costs the full stake.',
                    'At 80% payout you must win ~56% of trades to break even.',
                    'You need a real edge to beat the payout gap.',
                ])],

            ['Choosing Your Expiry Time', 'beginner', '08:45', 4, false, '4CQzOXbkLqY',
                'Expiry is the most underrated setting on the screen. The right choice depends on your strategy, not your mood.',
                "## Short expiries: fast and noisy\nVery short expiries (seconds to a minute) are dominated by random noise. Price can move against you for reasons that have nothing to do with your analysis. They feel exciting but are punishing for beginners.\n\n## Longer expiries: room to be right\nGiving your idea more time lets genuine direction express itself and smooths out random ticks. If your reasoning is sound, a longer expiry usually serves it better.\n\n## Match expiry to your reason\nA bounce off a minute-chart level deserves a short expiry. A broader trend call deserves a longer one. The expiry should fit the timeframe of your analysis.\n\n## Consistency beats variety\nPick one or two expiries and learn them deeply. Constantly changing expiry makes it impossible to know whether your strategy actually works.\n\n".$this->keyTakeaways([
                    'Short expiries are mostly noise — risky for beginners.',
                    'Longer expiries let real direction show.',
                    'Match expiry to the timeframe of your reasoning.',
                ])],

            ['Reading the Trading Screen', 'beginner', '11:20', 5, false, '_YVQN6_nkfs',
                'A quick tour of every element on the Cryptocoinex trade screen so you always know where to look.',
                "## The chart\nThe centre is your price chart. Switch between candles, line and area, change the interval, and toggle indicators like moving averages and RSI from the top bar.\n\n## The price pill and live dot\nThe current price flashes green on up-ticks and red on down-ticks. A pulsing dot confirms you are receiving live data.\n\n## The bottom controls\nHere you set your stake, see your projected profit on the BUY and SELL buttons, choose expiry, and watch the auto-close countdown ring.\n\n## The deals panel\nThe right panel tracks your open positions with a live time-remaining ring and a running win/loss summary, plus your recent history.\n\n## Balance and navigation\nYour PRACTICE\$ balance is always visible at the top. The left rail jumps to history, journal, leaderboard, tournaments, your profile and this academy.\n\n".$this->keyTakeaways([
                    'Chart, price pill, bottom controls, deals panel, balance.',
                    'Green/red flashes show price direction in real time.',
                    'The right panel tracks open trades and history.',
                ])],

            ['Your First Practice Trade', 'beginner', '14:10', 5, true, 'Lh_ofHbhdVU',
                'Put it all together. Follow this guided first trade on your demo balance with zero pressure.',
                "## Before you click\nPick a familiar asset, set a tiny stake (well under 5% of balance), and choose a comfortable expiry. The goal is process, not profit.\n\n## Form a one-sentence reason\nSay out loud why you are entering: \"Price is trending up and just bounced, so I expect higher.\" If you cannot finish the sentence, do not trade.\n\n## Place and observe\nClick your direction, then simply watch. Notice the entry line, the countdown ring, and how the open-trade P&L updates as price moves.\n\n## Review honestly\nWin or lose, ask: was my reason valid? Did I follow my plan? Outcome and decision quality are different things — judge the decision.\n\n## Repeat deliberately\nRepetition on demo builds the instincts that keep real money safe later. Treat every practice trade as if it were real.\n\n".$this->keyTakeaways([
                    'Tiny stake, familiar asset, clear one-sentence reason.',
                    'Watch the mechanics: entry line, ring, live P&L.',
                    'Judge your decision quality, not just the result.',
                ])],

            ['Common Beginner Mistakes to Avoid', 'base', '13:05', 5, false, '0hEhPY9n5lc',
                'Most new traders lose for the same handful of reasons. Spot them early and skip years of expensive lessons.',
                "## Overtrading\nClicking constantly out of boredom or excitement is the fastest way to drain a balance. Quality beats quantity — a few good trades beat fifty random ones.\n\n## Revenge trading\nAfter a loss, the urge to \"win it back\" immediately leads to bigger, sloppier trades. The market does not owe you anything. Step away instead.\n\n## Risking too much\nStaking a huge chunk of your balance on one trade means a short losing streak — which is inevitable — wipes you out. Keep each stake small.\n\n## No plan\nTrading without rules for entries, stakes and stops is gambling. Write your plan down and follow it.\n\n## Ignoring the demo\nSkipping practice to chase real money fast almost always backfires. The demo is where mistakes are free.\n\n".$this->keyTakeaways([
                    'Overtrading and revenge trading destroy balances.',
                    'Keep stakes small so streaks cannot ruin you.',
                    'Trade a written plan, not your emotions.',
                ])],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRACK 2 — FIRST STEPS
    // ════════════════════════════════════════════════════════════════════════
    private function firstSteps(): array
    {
        return [
            ['Demo vs Real: Why Practice Comes First', 'beginner', '10:00', 4, true, 'M2ccf0_vkZY',
                'A demo balance is the single most valuable tool a beginner has. Here is how to use it like a professional instead of a tourist.',
                "## The demo is a flight simulator\nPilots train for hundreds of hours in simulators before touching a real cockpit. Your PRACTICE\$ balance is exactly that: a risk-free place to build skill and habits.\n\n## Treat demo money seriously\nThe biggest mistake is treating demo funds as fake and trading recklessly. Trade your demo as if every PRACTICE\$ were real — otherwise the habits you build will not transfer.\n\n## What to prove on demo\nBefore even thinking about real money, prove you can follow a plan, keep stakes small, and stay flat or green over many trades. Consistency, not one lucky run, is the bar.\n\n## Graduating\nThere is no rush. Many successful traders spend months on demo. Move on only when your process — not your luck — is reliably profitable.\n\n".$this->keyTakeaways([
                    'Demo is a free flight simulator for trading.',
                    'Trade demo exactly as seriously as real money.',
                    'Graduate on consistency, never on one lucky streak.',
                ])],

            ['Understanding Markets: Crypto, Forex and Indices', 'beginner', '12:30', 5, true, 'vFpveDVZvXA',
                'Different assets behave differently. Knowing the personality of each market helps you pick ones that suit you.',
                "## Crypto\nCryptocurrencies like Bitcoin and Ethereum trade 24/7 and are famously volatile. Big moves create opportunity but also punish careless risk. Great for those who like action — dangerous for the impulsive.\n\n## Forex\nForeign-exchange pairs (EUR/USD, GBP/USD) are the largest market on earth. They tend to move more smoothly and respect technical levels well, which many beginners find easier to read.\n\n## Indices and commodities\nIndices like the S&P 500 track baskets of companies; commodities like gold and oil respond to global supply and demand. They add variety and often trend cleanly.\n\n## Pick a personality that fits you\nThere is no best market — only the one that matches your temperament and the time you can give it. Start with one and learn it deeply.\n\n".$this->keyTakeaways([
                    'Crypto: volatile, 24/7, high opportunity and risk.',
                    'Forex: huge, smoother, respects technical levels.',
                    'Pick one market that fits your temperament and master it.',
                ])],

            ['What Is Bitcoin and Why Does It Move?', 'beginner', '13:45', 5, false, '000UtKtm7kU',
                'Bitcoin is the asset that started the crypto revolution. Understand the basics before you ever trade it.',
                "## A digital, decentralised asset\nBitcoin is money that no single bank or government controls. It lives on a public ledger called a blockchain, maintained by thousands of computers worldwide.\n\n## Fixed supply, shifting demand\nThere will only ever be 21 million bitcoin. With supply capped, price is driven almost entirely by changing demand — which is why it can move so dramatically.\n\n## What drives the price\nNews, regulation, big institutional buyers, market sentiment and broader risk appetite all push Bitcoin around. It often moves fast and emotionally.\n\n## Trading vs investing\nInvestors buy and hold for years; traders profit from short-term swings. In this simulator you are trading direction, so you care about momentum and levels, not long-term belief.\n\n".$this->keyTakeaways([
                    'Bitcoin is decentralised with a fixed 21M supply.',
                    'Capped supply means demand drives big price swings.',
                    'Trading focuses on short-term direction, not holding.',
                ])],

            ['How Crypto Markets Actually Work', 'base', '15:20', 6, false, 'xTX8u8VCguk',
                'Behind every price tick is a marketplace of buyers and sellers. Understanding market mechanics makes charts make sense.',
                "## Order books and liquidity\nExchanges match buyers and sellers through an order book. Liquid markets have many orders close together, so price moves smoothly. Thin markets gap and whipsaw.\n\n## Bid, ask and the spread\nThe bid is the highest price buyers will pay; the ask is the lowest sellers accept. The gap between them is the spread — a hidden cost that is wider in illiquid markets.\n\n## Volatility is normal\nCrypto can move several percent in minutes. That volatility is the source of opportunity but demands tight risk control.\n\n## Why structure matters\nBecause price reflects collective buying and selling pressure, chart structure — trends, levels, patterns — is really a map of crowd behaviour. That is what makes technical analysis useful.\n\n".$this->keyTakeaways([
                    'Order books match buyers and sellers; liquidity = smooth price.',
                    'The spread is a hidden cost, wider in thin markets.',
                    'Charts are a map of crowd buying and selling pressure.',
                ])],

            ['Building a Trading Routine', 'base', '11:10', 4, false, 'R8aAPHvHiKU',
                'Consistency comes from routine, not inspiration. A simple repeatable process keeps emotion out of your trading.',
                "## Prepare before you trade\nGlance at the overall trend and note key levels before placing anything. Five minutes of preparation prevents most impulsive mistakes.\n\n## Define your session\nDecide in advance how long you will trade and how many trades you will take. Open-ended sessions invite overtrading.\n\n## One setup at a time\nMaster a single setup before adding others. Knowing one thing deeply beats knowing ten things shallowly.\n\n## Always journal\nRecord every trade — asset, reason, result, and how you felt. Your journal is where real improvement happens. Use the Journal page in this app for exactly this.\n\n## Review weekly\nOnce a week, read back your trades. Patterns in your wins and losses will jump out and guide what to fix.\n\n".$this->keyTakeaways([
                    'Prepare, define your session, trade one setup.',
                    'Journal every trade — reason, result, emotion.',
                    'Weekly reviews turn experience into improvement.',
                ])],

            ['Setting Realistic Goals', 'beginner', '09:55', 4, false, 'bgCGE7jt4-g',
                'Unrealistic expectations destroy more beginners than bad strategies. Set goals that keep you in the game.',
                "## Forget getting rich quick\nStories of overnight fortunes are survivorship bias. Sustainable trading is about small, repeatable gains that compound over time.\n\n## Process goals beat profit goals\nAim to follow your plan on every trade, keep stakes small, and journal consistently. Master the process and profits follow; chase profits and the process collapses.\n\n## Expect losing streaks\nEven a good strategy loses several trades in a row regularly. Planning for streaks emotionally and financially is what separates survivors from quitters.\n\n## Measure in months, not minutes\nJudge your progress over dozens of trades, not single results. One trade tells you nothing; a hundred trades tell you everything.\n\n".$this->keyTakeaways([
                    'Get-rich-quick stories are survivorship bias.',
                    'Set process goals, not profit goals.',
                    'Expect losing streaks and judge progress over months.',
                ])],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRACK 3 — MARKET ANALYSIS
    // ════════════════════════════════════════════════════════════════════════
    private function marketAnalysis(): array
    {
        return [
            ['How to Read Candlestick Charts', 'beginner', '14:30', 6, true, 'AOz1YPOKvEs',
                'Candlesticks pack four prices into one shape. Once you can read them at a glance, charts come alive.',
                "## Anatomy of a candle\nEach candle shows four prices for its period: open, high, low and close. The body spans open-to-close; the thin wicks reach to the high and low.\n\n## Colour tells the story\nA green (up) candle closed higher than it opened — buyers won the period. A red (down) candle closed lower — sellers won. A glance at colour reveals who is in control.\n\n## Bodies and wicks\nLong bodies signal strong conviction; small bodies signal indecision. Long wicks show a price was rejected — the market tried a level and was pushed back.\n\n## Reading sequences\nCandles only gain meaning in context. A run of strong green candles is momentum; alternating colours with long wicks is a battle. You are reading a story, not isolated shapes.\n\n".$this->keyTakeaways([
                    'Each candle shows open, high, low, close.',
                    'Green = buyers won the period; red = sellers won.',
                    'Long wicks show rejection; read candles in sequence.',
                ])],

            ['Essential Candlestick Patterns', 'base', '16:15', 6, true, 'L16yPlr8jyM',
                'A handful of candlestick patterns appear again and again. Learn the reliable ones and ignore the noise.',
                "## The doji — indecision\nA doji has almost no body: open and close are nearly equal. It signals a pause or potential turning point, especially after a strong move.\n\n## The hammer — rejection of lows\nA hammer has a small body and a long lower wick. It shows sellers pushed price down but buyers slammed it back up — often a bullish reversal hint near support.\n\n## Engulfing — a shift in control\nA bullish engulfing candle completely covers the prior red candle, signalling buyers have seized control. The bearish version is the mirror image.\n\n## Context is everything\nNo pattern works in a vacuum. A hammer at support is meaningful; the same shape mid-range is noise. Always read patterns alongside trend and levels.\n\n".$this->keyTakeaways([
                    'Doji = indecision; hammer = rejection of lows.',
                    'Engulfing candles signal a shift in control.',
                    'Patterns only matter at meaningful levels.',
                ])],

            ['Identifying Trends: Up, Down and Sideways', 'beginner', '12:50', 5, true, 'YXgEGdX_jC4',
                'The trend is your strongest ally. Learning to classify it in seconds is a foundational skill.',
                "## The three states of price\nAt any moment price is doing one of three things: trending up, trending down, or moving sideways in a range. Your whole approach should change depending on which.\n\n## Higher highs and higher lows\nAn uptrend makes a staircase of higher highs and higher lows. A downtrend makes lower highs and lower lows. Spotting this structure is most of technical analysis.\n\n## Trade with the trend\nBeginners profit most by predicting in the direction of the prevailing trend. \"The trend is your friend\" survives because fighting strong momentum is a losing game.\n\n## Ranges need a different plan\nIn a sideways range, price bounces between support and resistance. Here you fade the edges rather than chase momentum. Misreading a range as a trend is a classic, costly error.\n\n".$this->keyTakeaways([
                    'Price trends up, trends down, or ranges sideways.',
                    'Uptrend = higher highs/lows; downtrend = lower highs/lows.',
                    'Trade with trends; fade the edges of ranges.',
                ])],

            ['Support and Resistance Explained', 'beginner', '15:00', 6, true, '1LdqVx3gmI0',
                'Support and resistance are the most useful concept in all of charting. Master them and the chart becomes a map.',
                "## What they are\nSupport is a price floor where buyers repeatedly step in. Resistance is a ceiling where sellers repeatedly appear. They are zones of remembered decisions, not exact lines.\n\n## Why they work\nTraders remember levels. When price returns to a spot where it bounced before, the same buyers and sellers tend to act again — making the level a self-fulfilling magnet.\n\n## Bounces and breaks\nPrice either bounces off a level or breaks through it. A clean bounce is a reversal opportunity; a decisive break often leads to a continuation in the breakout direction.\n\n## Drawing them well\nMark zones, not pixel-perfect lines, and use the most obvious levels — the ones a child could point to. The clearer the level, the more traders watch it, and the better it works.\n\n".$this->keyTakeaways([
                    'Support = floor of buyers; resistance = ceiling of sellers.',
                    'Levels work because traders remember and react to them.',
                    'Trade bounces off levels and breaks through them.',
                ])],

            ['Drawing and Using Trend Lines', 'base', '13:20', 5, false, 'msT7cHS08U0',
                'A well-drawn trend line turns a messy chart into a clear story of who is winning.',
                "## Connecting the dots\nAn uptrend line connects rising lows; a downtrend line connects falling highs. You need at least two points to draw one and a third touch to confirm it.\n\n## What a trend line shows\nAs long as price respects the line, the trend is intact. The line acts as dynamic support or resistance, often giving clean entry points on each touch.\n\n## Breaks signal change\nWhen price decisively breaks a trend line that has held several times, it warns the trend may be weakening or reversing — a powerful early signal.\n\n## Keep it honest\nDo not force a line through prices that do not fit. If you have to ignore obvious candles to make it work, the line is not real. The best lines are obvious.\n\n".$this->keyTakeaways([
                    'Uptrend lines connect rising lows; downtrend lines falling highs.',
                    'Respected lines act as dynamic support/resistance.',
                    'A decisive break warns of a possible trend change.',
                ])],

            ['Price Action: Trading What You See', 'base', '17:40', 7, false, 'BSqnSn8ObZA',
                'Price action means reading raw price movement without cluttering the chart with indicators. It is trading in its purest form.',
                "## Indicators lag, price leads\nEvery indicator is built from past price, so it always lags. Price action reads the market directly, giving you the earliest possible read on what is happening.\n\n## The clues price gives\nMomentum (speed of candles), rejection (long wicks), and structure (highs and lows) tell you who is winning right now. These three clues underpin most price-action decisions.\n\n## Clean charts, clear minds\nA chart drowning in indicators creates analysis paralysis. Many professionals trade with little more than price, levels and a trend line.\n\n## Combine, do not clutter\nPrice action pairs beautifully with support/resistance: a rejection candle at a key level is far stronger than either signal alone. Build confluence, not clutter.\n\n".$this->keyTakeaways([
                    'Price leads; indicators lag because they are built from price.',
                    'Read momentum, rejection and structure directly.',
                    'Combine price action with key levels for confluence.',
                ])],

            ['Understanding Market Structure', 'advanced', '18:10', 7, false, 'Mzf2MujmX9Q',
                'Market structure is the framework that ties trends, levels and patterns into one coherent view of the market.',
                "## Swings build structure\nMarkets move in swings — pushes and pullbacks. The sequence of swing highs and swing lows defines the structure and, therefore, the trend.\n\n## Breaks of structure\nWhen price makes a new high in an uptrend, structure is intact. When it fails to and instead breaks a recent swing low, that \"break of structure\" warns the trend may be turning.\n\n## Pullbacks are opportunities\nWithin a healthy trend, pullbacks against the direction are where the best entries live — you join the trend at a discount rather than chasing it.\n\n## Zoom out for context\nStructure on a higher timeframe overrules the lower one. Always know the bigger-picture structure before acting on a small move, or you may trade against the dominant tide.\n\n".$this->keyTakeaways([
                    'Swing highs and lows define structure and trend.',
                    'A break of structure warns of a possible reversal.',
                    'Higher-timeframe structure overrules the lower one.',
                ])],

            ['Spotting Reversals Before They Happen', 'advanced', '16:50', 6, false, 'EVlQgmirnCg',
                'Catching a turn early is high-reward but high-risk. Learn the warning signs that a move is running out of steam.',
                "## Momentum fading\nWhen each new push makes less progress than the last — shrinking candle bodies, weaker highs — the trend is tiring. Fading momentum is the first whisper of a reversal.\n\n## Rejection at a level\nA strong rejection wick at a major support or resistance zone is a classic reversal clue, especially when it appears after an extended move.\n\n## Failure to continue\nWhen price tries to make a new high or low and immediately snaps back, the failed attempt often marks the turning point.\n\n## Wait for confirmation\nReversals are where overconfident traders get hurt. Demanding confirmation — a broken structure, an engulfing candle — keeps you from catching a falling knife.\n\n".$this->keyTakeaways([
                    'Fading momentum is the first sign of a turn.',
                    'Rejections at major levels often precede reversals.',
                    'Wait for confirmation — do not catch a falling knife.',
                ])],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRACK 4 — INDICATORS & TOOLS
    // ════════════════════════════════════════════════════════════════════════
    private function indicators(): array
    {
        return [
            ['Indicators 101: Moving Average, RSI and MACD', 'beginner', '15:30', 6, true, 'PhzO7iTBoMs',
                'A gentle introduction to the three indicators you will meet everywhere. Understand what each one is actually telling you.',
                "## What an indicator is\nAn indicator is a calculation drawn on your chart to highlight something price is doing — trend, momentum, or extremes. It is a lens, not a crystal ball.\n\n## Moving Average — trend\nA moving average smooths price into a single flowing line, making the underlying trend obvious. Price above a rising average is bullish context; below a falling one is bearish.\n\n## RSI — momentum and extremes\nThe Relative Strength Index measures how overbought or oversold price is on a 0–100 scale. It flags when a move may be stretched.\n\n## MACD — momentum shifts\nMACD compares two moving averages to reveal shifts in momentum, often signalling acceleration or fading of a move before price makes it obvious.\n\n## Use them as confirmation\nIndicators shine as confirmation of a price-action idea — never as a sole reason to trade. One indicator agreeing with the chart is worth ten contradicting it.\n\n".$this->keyTakeaways([
                    'Indicators are lenses on trend, momentum or extremes.',
                    'MA = trend, RSI = overbought/oversold, MACD = momentum shifts.',
                    'Use indicators to confirm price action, not replace it.',
                ])],

            ['Mastering Moving Averages', 'base', '14:00', 5, true, 'RrYSuEcf9ZE',
                'The moving average is the workhorse of technical analysis. Learn to use it for trend, support and signals.',
                "## Fast vs slow\nA short-period average (like MA20) hugs price and reacts quickly; a long one (MA50, MA200) is slower and shows the bigger trend. Using two together is powerful.\n\n## The slope is the signal\nA rising average means an uptrend; a falling one means a downtrend; a flat one means a range. The direction of the line is often more useful than its exact value.\n\n## Dynamic support and resistance\nIn trends, price frequently pulls back to a moving average and bounces. These pullbacks offer some of the cleanest trend-following entries.\n\n## Crossovers\nWhen a fast average crosses above a slow one, momentum is shifting up; a cross below signals down. Crossovers lag, so treat them as confirmation, not prediction.\n\n".$this->keyTakeaways([
                    'Short MAs react fast; long MAs show the big trend.',
                    'The slope reveals trend; pullbacks to the MA offer entries.',
                    'Crossovers confirm momentum shifts but lag price.',
                ])],

            ['The RSI Indicator in Depth', 'base', '13:40', 5, true, 'H5SWoOnLIXE',
                'RSI is the most popular momentum oscillator. Used well it spots exhaustion; used badly it bleeds accounts.',
                "## Reading the 0–100 scale\nRSI above 70 is traditionally \"overbought\" and below 30 \"oversold.\" These zones flag when a move may be stretched and due a pause or pullback.\n\n## Overbought is not \"sell now\"\nThe classic beginner trap: shorting just because RSI is high. In a strong trend RSI can stay overbought for a long time. It is a warning, not a trigger.\n\n## Divergence — the real edge\nWhen price makes a new high but RSI makes a lower high, momentum is fading even as price rises. This \"bearish divergence\" is one of RSI's most reliable signals.\n\n## Combine with levels\nAn oversold RSI at a major support zone is far stronger than an oversold RSI in mid-air. Confluence transforms RSI from noise into signal.\n\n".$this->keyTakeaways([
                    'RSI >70 overbought, <30 oversold — warnings, not triggers.',
                    'Strong trends can stay overbought for a long time.',
                    'Divergence and confluence with levels are RSI\'s real edge.',
                ])],

            ['Trading with MACD', 'base', '14:25', 5, false, 'Gs7cjnuByFA',
                'MACD turns two moving averages into a momentum gauge. Learn its three parts and the signals that matter.',
                "## The three components\nMACD has the MACD line, a signal line, and a histogram showing the gap between them. Together they reveal the strength and direction of momentum.\n\n## The classic crossover\nWhen the MACD line crosses above the signal line, upward momentum is building; a cross below signals downward momentum. It is the most-watched MACD event.\n\n## The histogram tells speed\nA growing histogram means momentum is accelerating; a shrinking one means it is fading — often before the lines actually cross. Watch it for early warnings.\n\n## Divergence again\nLike RSI, MACD can diverge from price: price makes a new extreme while MACD does not, hinting the move is running out of fuel.\n\n".$this->keyTakeaways([
                    'MACD = MACD line, signal line, histogram.',
                    'Line crossovers signal momentum direction.',
                    'A shrinking histogram warns momentum is fading early.',
                ])],

            ['Bollinger Bands and Volatility', 'base', '15:10', 6, false, 'KsUCmWpyqh8',
                'Bollinger Bands wrap price in a volatility envelope, showing when markets are calm, stretched, or about to move.',
                "## What the bands show\nBollinger Bands plot a moving average with an upper and lower band set by volatility. The bands widen when price is volatile and squeeze when it is quiet.\n\n## The squeeze\nWhen the bands pinch tightly together, volatility is low and a big move often follows. The squeeze does not tell you direction, only that energy is building.\n\n## Riding the bands\nIn strong trends, price can \"walk\" along the upper or lower band. Betting against a band-walk in a powerful trend is a common, costly mistake.\n\n## Mean reversion in ranges\nIn calm ranges, touches of the outer bands often snap back toward the middle. The same tool behaves differently depending on whether you are trending or ranging.\n\n".$this->keyTakeaways([
                    'Bands widen with volatility and squeeze when quiet.',
                    'A squeeze warns a big move is coming (not which way).',
                    'Bands mean-revert in ranges but get walked in trends.',
                ])],

            ['Combining Indicators Without Clutter', 'advanced', '16:00', 6, false, '1QLEdmHJGH0',
                'More indicators do not mean better trades. Learn to combine a few complementary tools instead of stacking redundant ones.',
                "## Avoid redundancy\nRSI, MACD and Stochastics all measure momentum — stacking them just gives you three versions of the same opinion. Choose one per job.\n\n## One of each type\nA clean toolkit pairs a trend tool (a moving average), a momentum tool (RSI or MACD), and structure (support/resistance). Each answers a different question.\n\n## Seek confluence\nThe goal is agreement: trend up, momentum up, price bouncing off support. When independent tools point the same way, your odds improve dramatically.\n\n## When they disagree, wait\nConflicting signals are the market telling you it is unclear. The professional response to confusion is to do nothing, not to trade harder.\n\n".$this->keyTakeaways([
                    'Do not stack indicators that measure the same thing.',
                    'Use one trend tool, one momentum tool, plus structure.',
                    'Trade on confluence; when tools disagree, wait.',
                ])],

            ['Volume: The Fuel Behind Moves', 'advanced', '13:15', 5, false, 'fFmcONKy3bA',
                'Volume reveals the conviction behind a price move. A move on heavy volume means far more than the same move on thin trade.',
                "## Volume measures participation\nVolume counts how much was traded in a period. High volume means many participants agree; low volume means few care. It is the conviction behind price.\n\n## Confirming breakouts\nA breakout on heavy volume is far more trustworthy than one on light volume, which often fails and reverses. Volume separates real breaks from traps.\n\n## Exhaustion spikes\nA huge volume spike after a long move can mark exhaustion — the last buyers piling in just before a reversal. Climax volume is a warning to be alert.\n\n## Volume and trend health\nA healthy trend sees volume expand in its direction and shrink on pullbacks. When pullbacks start coming on rising volume, the trend may be in trouble.\n\n".$this->keyTakeaways([
                    'Volume measures conviction behind a move.',
                    'Breakouts on heavy volume are far more reliable.',
                    'Climax volume can mark exhaustion and reversal.',
                ])],

            ['Building Your Personal Indicator Toolkit', 'advanced', '12:35', 5, false, 'DR_CiMf_q1Y',
                'The best toolkit is small, personal and battle-tested. Here is how to assemble one you actually trust.',
                "## Start from your strategy\nIndicators should serve a specific decision in your plan, not decorate the chart. Ask \"what question does this answer?\" before adding anything.\n\n## Test one change at a time\nAdd or remove a single tool, then trade it on demo for many trades before judging. Changing everything at once teaches you nothing.\n\n## Trust comes from repetition\nYou only trust a tool after seeing it work — and fail — many times. That earned trust is what lets you act on its signals under pressure.\n\n## Keep it minimal\nMost consistently profitable traders use surprisingly few tools. If you cannot explain why each indicator is on your chart, remove it.\n\n".$this->keyTakeaways([
                    'Every tool must answer a question in your plan.',
                    'Test one change at a time on demo.',
                    'Keep the toolkit minimal and earn trust through repetition.',
                ])],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRACK 5 — TRADING STRATEGIES
    // ════════════════════════════════════════════════════════════════════════
    private function strategies(): array
    {
        return [
            ['The Trend-Following Strategy', 'beginner', '14:50', 6, true, 'gsP7iVmxUGM',
                'Trend following is the friendliest strategy for beginners: align with the dominant move and let momentum do the work.',
                "## The core idea\nIdentify the prevailing trend, then only predict in its direction. You are not trying to be clever — you are riding a force that is already in motion.\n\n## Finding the trend\nUse structure (higher highs/lows) or a moving average slope to classify the trend in seconds. If it is unclear, there is no trend to follow — stand aside.\n\n## Entering on pullbacks\nThe best trend entries come on pullbacks against the move, near a moving average or support in an uptrend. You join the trend at a discount instead of chasing the high.\n\n## When to skip\nTrend following fails in choppy ranges. If price is going sideways, this strategy will chop you up — recognise the range and switch off.\n\n".$this->keyTakeaways([
                    'Only predict in the direction of the dominant trend.',
                    'Enter on pullbacks, not by chasing extended moves.',
                    'Stand aside when there is no clear trend.',
                ])],

            ['Support and Resistance Bounce Strategy', 'beginner', '13:30', 5, true, 'N4ZS-NNURPM',
                'One of the most reliable beginner setups: trade clean bounces off well-tested support and resistance zones.',
                "## The setup\nMark an obvious support or resistance zone. When price returns to it and shows rejection — a wick, a reversal candle — you predict a bounce away from the level.\n\n## Why it works\nThese zones concentrate orders. As price arrives, the same buyers or sellers who acted before tend to act again, pushing price away and rewarding your entry.\n\n## Wait for the reaction\nDo not predict a bounce the instant price touches the level. Wait for price to actually react — a rejection candle is your green light, not the touch itself.\n\n## Managing the risk\nThe beauty of this setup is a clear invalidation: if price closes decisively through the level, your idea is wrong. Clean levels give clean decisions.\n\n".$this->keyTakeaways([
                    'Trade bounces off clear, well-tested levels.',
                    'Wait for a rejection reaction, not just a touch.',
                    'A decisive break through the level invalidates the idea.',
                ])],

            ['The Breakout Strategy', 'base', '15:45', 6, true, '38yZ7ETnnis',
                'Breakouts catch the explosive move when price escapes a level. High reward, but you must filter the fakeouts.',
                "## What a breakout is\nWhen price has been capped by a level and finally pushes decisively through it, energy that was building gets released — often into a fast, clean move.\n\n## Real vs fake\nMany breakouts fail. The strongest come on expanding volume and a decisive close beyond the level — not a brief poke that snaps back inside.\n\n## The retest entry\nA lower-risk approach waits for price to break out, then come back to retest the old level as new support or resistance before continuing. The retest filters out many fakeouts.\n\n## Where beginners go wrong\nChasing every breakout the instant price pokes through leads to constant fakeout losses. Patience and volume confirmation are what make this strategy profitable.\n\n".$this->keyTakeaways([
                    'Breakouts release energy built up at a level.',
                    'Confirm with volume and a decisive close, not a poke.',
                    'A retest of the broken level is a lower-risk entry.',
                ])],

            ['Trend-Line Trading in Practice', 'base', '16:20', 6, false, 'yHAC0xtBR2Q',
                'Turn trend lines into a complete strategy with clear entries, confirmation and invalidation.',
                "## Draw the obvious line\nConnect the clear rising lows (uptrend) or falling highs (downtrend). The line should be touched at least twice and ideally confirmed by a third reaction.\n\n## Entry on the touch\nIn an uptrend, predict upward as price pulls back to and bounces off the line. Each respected touch is a fresh, low-risk entry in the trend's direction.\n\n## Confirmation matters\nDo not anticipate the bounce blindly. Let price show a reaction at the line — a rejection candle — before committing.\n\n## The break is information\nWhen a trend line that has held several times finally breaks, the trend may be over. That break is both an exit signal and a potential reversal setup.\n\n".$this->keyTakeaways([
                    'Trade bounces off a well-respected trend line.',
                    'Wait for a reaction at the line before entering.',
                    'A clean break of the line warns the trend is ending.',
                ])],

            ['Combining Bollinger Bands with Trend', 'advanced', '17:00', 7, false, '3SsjD33ddtI',
                'Bollinger Bands plus trend context create a nuanced strategy that adapts to whether the market is calm or trending.',
                "## Read the regime first\nBefore using the bands, decide if the market is trending or ranging — the bands mean completely different things in each. This single judgement drives the whole approach.\n\n## In a range\nWhen price is ranging, touches of the outer bands often snap back to the middle. You fade the extremes, predicting reversion toward the average.\n\n## In a trend\nIn a strong trend, price walks the band in the trend's direction. Here you do the opposite — you go with the band-walk, not against it.\n\n## The squeeze as a heads-up\nA tight squeeze warns a big move is coming. Combine the squeeze with structure to anticipate the breakout direction and be ready.\n\n".$this->keyTakeaways([
                    'Decide trend vs range before using the bands.',
                    'Fade the bands in ranges; ride them in trends.',
                    'A squeeze signals a coming move — prepare for the break.',
                ])],

            ['Confluence: Stacking the Odds', 'advanced', '15:55', 6, false, 'a_PpkF7Q9NY',
                'The best trades happen when several independent signals agree. Confluence is how professionals separate A-grade setups from noise.',
                "## What confluence means\nConfluence is when multiple unrelated factors point the same way: a support level, a trend, a moving average and a rejection candle all aligning at one spot.\n\n## Why it works\nEach signal alone has modest odds. When several independent signals agree, the probability of being right rises sharply — and you can act with real conviction.\n\n## Build a checklist\nDefine the factors you want to see — trend direction, a level, a momentum read, a candle signal. Only take trades where enough boxes are ticked.\n\n## Quality over quantity\nConfluence naturally reduces how many trades you take, and that is the point. Fewer, higher-quality trades beat a flood of marginal ones.\n\n".$this->keyTakeaways([
                    'Confluence = several independent signals agreeing.',
                    'Agreement of independent factors raises your odds sharply.',
                    'A checklist enforces fewer, higher-quality trades.',
                ])],

            ['Building Your Own Trading Plan', 'advanced', '18:30', 7, false, '7jnBYTb9UUc',
                'A written plan is what turns scattered knowledge into consistent results. Here is how to build one you will actually follow.',
                "## Define your setup\nWrite down the exact conditions that make you trade: the trend state, the level, the confirmation. If a trade does not match, you do not take it. Period.\n\n## Fix your risk rules\nDecide your maximum stake per trade (a small % of balance) and your maximum number of trades per session. These rules protect you from yourself on bad days.\n\n## Plan your review\nDecide how and when you will journal and review. A plan without review never improves — the feedback loop is the engine of progress.\n\n## Follow it like a pro\nThe hardest part is not writing the plan but obeying it when emotions scream otherwise. Discipline to follow a good plan is the real edge.\n\n".$this->keyTakeaways([
                    'Write the exact conditions that define a valid trade.',
                    'Fix stake size and trade count rules in advance.',
                    'Review regularly and follow the plan under pressure.',
                ])],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRACK 6 — RISK & PSYCHOLOGY
    // ════════════════════════════════════════════════════════════════════════
    private function riskPsychology(): array
    {
        return [
            ['Risk Management: The Skill That Keeps You Alive', 'beginner', '14:00', 6, true, 'dNyZ-jGn02s',
                'Most traders fail not from bad analysis but from bad risk control. This is the single most important lesson in the academy.',
                "## Why risk comes first\nYou can be right about direction and still go broke if you risk too much. Survival is the prerequisite for success — you cannot win the game if you are out of it.\n\n## The small-stake rule\nRisk only a small slice — say 1–3% — of your balance on any single trade. With small stakes, even a brutal losing streak only dents your balance, leaving you able to recover.\n\n## Streaks are guaranteed\nEven a strong strategy strings together several losses regularly. The only question is whether your stake size lets you survive them. Plan for streaks before they arrive.\n\n## Protect the downside, let the upside come\nIf you take care of the losses, the wins take care of themselves. Obsess over not blowing up, and profitability becomes possible.\n\n".$this->keyTakeaways([
                    'You can be right and still blow up by over-risking.',
                    'Risk only 1–3% of balance per trade.',
                    'Small stakes let you survive the inevitable losing streaks.',
                ])],

            ['Position Sizing for Survival', 'base', '13:20', 5, true, 'm535_tYLgRI',
                'How much to stake is a more important question than which direction to predict. Position sizing is risk management in action.',
                "## Size, not luck, decides survival\nTwo traders with the same win-rate can have opposite fates purely from stake size. The one who bets small survives the streaks; the one who bets big does not.\n\n## A simple rule\nPick a fixed small percentage of your balance per trade and stick to it. As your balance grows the stake grows with it; as it shrinks the stake shrinks, protecting you automatically.\n\n## Never \"size up to recover\"\nAfter losses, the temptation is to bet bigger to win it back fast. This is exactly backwards — bigger stakes after losses is how accounts die. Keep sizing constant.\n\n## Boredom is the price of survival\nProper position sizing feels almost boringly slow when you are winning. That patience is precisely what keeps you in the game long-term.\n\n".$this->keyTakeaways([
                    'Stake size matters more than direction calls.',
                    'Use a fixed small % of balance every trade.',
                    'Never increase size to chase back losses.',
                ])],

            ['Mastering Trading Psychology', 'base', '16:40', 6, true, '9VuHH0kts3c',
                'Your biggest opponent is not the market — it is your own mind. Fear and greed sink more traders than any strategy flaw.',
                "## Fear and greed\nFear makes you exit good trades early and skip valid setups. Greed makes you over-stake and overstay. Recognising which emotion is talking is the first step to mastering it.\n\n## The tilt spiral\nAfter a painful loss, many traders \"tilt\" — trading angrily and recklessly to get even. Tilt turns one small loss into a disaster. The cure is to stop and walk away.\n\n## Process over outcome\nA good decision can lose and a bad decision can win on any single trade. Judging yourself by process, not isolated outcomes, keeps emotion from hijacking you.\n\n## Routines tame emotion\nA fixed routine, small stakes and a journal create structure that emotion cannot easily override. Discipline is built, not born.\n\n".$this->keyTakeaways([
                    'Fear and greed cause most trading mistakes.',
                    'Tilt turns one loss into many — stop and walk away.',
                    'Judge process, not single outcomes; routines tame emotion.',
                ])],

            ['The Math of Winning and Losing', 'advanced', '15:25', 6, false, 'dOZ6g_B59zU',
                'Trading is a numbers game. Understanding the simple math behind it frees you from emotional reactions to single trades.',
                "## Win-rate and payout together\nProfit depends on win-rate and payout combined, not either alone. At an 80% payout you need roughly a 56% win-rate to break even — a number worth memorising.\n\n## Expectancy is everything\nExpectancy is your average profit per trade over many trades. A positive expectancy, repeated with discipline, is the entire game. A negative one guarantees slow ruin.\n\n## Why one trade means nothing\nWith a positive edge, any single trade is still close to a coin flip. The edge only reveals itself across dozens or hundreds of trades — which is why patience pays.\n\n## Let the numbers calm you\nWhen you truly accept that losses are a normal, expected cost of a profitable system, individual losses stop stinging. The math, internalised, becomes emotional armour.\n\n".$this->keyTakeaways([
                    'Profit depends on win-rate and payout together.',
                    'Positive expectancy repeated with discipline is the whole game.',
                    'Single trades are noise; the edge shows over many trades.',
                ])],

            ['Discipline: Turning Knowledge into Results', 'advanced', '17:15', 7, false, 'KjCE5IR1hsc',
                'Everyone knows what they should do. The traders who succeed are the ones who actually do it, trade after trade. That is discipline.',
                "## The knowing-doing gap\nMost losing traders know the rules — small stakes, trade the plan, no revenge trading. They lose because they do not follow them under pressure. Closing that gap is everything.\n\n## Make rules automatic\nThe fewer in-the-moment decisions you make, the less emotion can interfere. Predefine your stake, your setups and your session length so the heat of the moment has nothing to corrupt.\n\n## Use the tools you have\nThis app gives you a journal, history, and small-stake controls precisely to build discipline. Track every trade and review weekly — the data does not lie.\n\n## Discipline compounds\nEach time you follow your plan despite the urge not to, the habit strengthens. Over hundreds of trades, that compounding discipline becomes your durable edge.\n\n".$this->keyTakeaways([
                    'Success is doing what you already know, every trade.',
                    'Predefine decisions so emotion has nothing to corrupt.',
                    'Journal, review, and let disciplined habits compound.',
                ])],
        ];
    }
}
