<?php

namespace App\Services\Trading;

use App\Models\Trading\Achievement;
use App\Models\User;

class AchievementService
{
    /**
     * Catalogue of all badges: type => [title, description, icon].
     */
    public const CATALOG = [
        'first_trade' => ['First Blood', 'Placed your very first trade.', 'fa-flag-checkered'],
        'win_streak_3' => ['Hat Trick', 'Won 3 trades in a row.', 'fa-fire'],
        'win_streak_5' => ['On Fire', 'Won 5 trades in a row.', 'fa-fire-flame-curved'],
        'profit_master' => ['Profit Master', 'Reached over 5,000 USD net profit.', 'fa-sack-dollar'],
        'btc_trader' => ['BTC Hodler', 'Placed 10 Bitcoin trades.', 'fa-bitcoin-sign'],
        'risk_manager' => ['Risk Manager', 'Placed 10+ trades, never risking over 25% of balance.', 'fa-shield-halved'],
        'comeback_kid' => ['Comeback Kid', 'Recovered from under 500 to over 5,000 USD.', 'fa-arrow-trend-up'],
        'century' => ['Centurion', 'Placed 100 trades.', 'fa-medal'],
    ];

    public function __construct(
        private readonly NotificationService $notifications
    ) {}

    /**
     * Evaluate all criteria for a user and award any newly-earned badges.
     *
     * @return array<int, Achievement> newly awarded achievements
     */
    public function evaluate(User $user): array
    {
        $existing = $user->achievements()->pluck('type')->all();
        $stats = $this->stats($user);
        $awarded = [];

        foreach ($this->earnedTypes($stats) as $type) {
            if (in_array($type, $existing, true)) {
                continue;
            }
            $awarded[] = $this->award($user, $type);
        }

        return $awarded;
    }

    public function award(User $user, string $type): Achievement
    {
        [$title, $description, $icon] = self::CATALOG[$type] ?? [$type, null, 'fa-award'];

        $achievement = Achievement::firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            ['title' => $title, 'description' => $description, 'icon' => $icon, 'achieved_at' => now(), 'created_at' => now()]
        );

        if ($achievement->wasRecentlyCreated) {
            $this->notifications->notify(
                $user->id,
                'achievement_earned',
                "Achievement unlocked: {$title}",
                $description,
                route('trade.profile'),
                $icon,
                ['type' => $type]
            );
        }

        return $achievement;
    }

    /**
     * Which badge types the user currently qualifies for (regardless of whether already awarded).
     *
     * @return array<int, string>
     */
    private function earnedTypes(array $s): array
    {
        $types = [];
        if ($s['total'] >= 1) {
            $types[] = 'first_trade';
        }
        if ($s['win_streak'] >= 3) {
            $types[] = 'win_streak_3';
        }
        if ($s['win_streak'] >= 5) {
            $types[] = 'win_streak_5';
        }
        if ($s['net_pnl'] > 5000) {
            $types[] = 'profit_master';
        }
        if ($s['btc_trades'] >= 10) {
            $types[] = 'btc_trader';
        }
        if ($s['settled'] >= 10 && $s['max_risk_pct'] <= 25) {
            $types[] = 'risk_manager';
        }
        if ($s['min_balance'] < 500 && $s['peak_balance'] > 5000) {
            $types[] = 'comeback_kid';
        }
        if ($s['total'] >= 100) {
            $types[] = 'century';
        }

        return $types;
    }

    /**
     * Aggregate the figures the criteria need.
     */
    public function stats(User $user): array
    {
        $trades = $user->trades()->with('asset')->orderBy('opened_at')->get();
        $settled = $trades->whereIn('status', ['won', 'lost', 'tie']);

        $netPnl = (int) $settled->sum(fn ($t) => ((int) ($t->payout_amount ?? 0)) - (int) $t->stake);

        // Current win streak (from most-recent settled backwards)
        $streak = 0;
        foreach ($settled->sortByDesc('settled_at')->values() as $t) {
            if ($t->status === 'won') {
                $streak++;
            } else {
                break;
            }
        }

        $wallet = $user->tradingWallet;

        return [
            'total' => $trades->count(),
            'settled' => $settled->count(),
            'wins' => $settled->where('status', 'won')->count(),
            'net_pnl' => $netPnl,
            'win_streak' => $streak,
            'btc_trades' => $trades->filter(fn ($t) => str_starts_with($t->asset?->symbol ?? '', 'BTC'))->count(),
            'max_risk_pct' => $this->maxRiskPct($user),
            'peak_balance' => (int) ($wallet?->peak_balance ?? 0),
            'min_balance' => $this->minBalance($user),
        ];
    }

    /**
     * The largest stake-as-%-of-balance the student has ever risked, using the
     * balance recorded just before each stake hold in the ledger.
     */
    private function maxRiskPct(User $user): float
    {
        $wallet = $user->tradingWallet;
        if (! $wallet) {
            return 0;
        }
        $max = 0.0;
        foreach ($wallet->entries()->where('type', 'stake_hold')->get() as $e) {
            // balance_after is post-debit; balance before = balance_after + |amount|
            $before = $e->balance_after + abs($e->amount);
            if ($before > 0) {
                $max = max($max, abs($e->amount) / $before * 100);
            }
        }

        return round($max, 2);
    }

    private function minBalance(User $user): int
    {
        $wallet = $user->tradingWallet;
        if (! $wallet) {
            return 0;
        }

        return (int) $wallet->entries()->min('balance_after');
    }
}
