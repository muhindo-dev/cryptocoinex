<?php

namespace App\Services\Trading;

use App\Models\Trading\LeaderboardSnapshot;
use App\Models\Trading\Trade;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    public const PERIODS = ['weekly', 'monthly', 'all_time'];

    /**
     * Ranked leaderboard rows for a period (cached briefly).
     *
     * @return Collection<int, array{user_id:int,name:string,avatar:?string,trades_count:int,win_rate:float,net_pnl:int,peak_balance:int,score:int,rank:int}>
     */
    public function ranked(string $period = 'all_time', int $limit = 100): Collection
    {
        $period = in_array($period, self::PERIODS, true) ? $period : 'all_time';
        $ttl = $period === 'weekly' ? 600 : ($period === 'monthly' ? 1800 : 3600);

        return Cache::remember("leaderboard:{$period}", $ttl, fn () => $this->compute($period, $limit));
    }

    /**
     * Compute the leaderboard live from trade data.
     */
    public function compute(string $period, int $limit = 100): Collection
    {
        $since = $this->since($period);

        $rows = Trade::query()
            ->selectRaw('user_id')
            ->selectRaw('count(*) as trades_count')
            ->selectRaw("sum(case when status = 'won' then 1 else 0 end) as wins")
            ->selectRaw("sum(case when status in ('won','lost','tie') then 1 else 0 end) as settled")
            ->selectRaw('sum(coalesce(payout_amount,0) - stake) as net_pnl')
            ->whereIn('status', ['won', 'lost', 'tie'])
            ->when($since, fn ($q) => $q->where('settled_at', '>=', $since))
            ->groupBy('user_id')
            ->get();

        $users = User::whereIn('id', $rows->pluck('user_id'))
            ->with('tradingWallet')
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($r) use ($users) {
                $user = $users[$r->user_id] ?? null;
                if (! $user) {
                    return null;
                }
                $settled = (int) $r->settled;
                $winRate = $settled > 0 ? round((int) $r->wins / $settled * 100, 2) : 0.0;
                $netPnl = (int) $r->net_pnl;
                $peak = (int) ($user->tradingWallet?->peak_balance ?? 0);

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar_url,
                    'country' => $user->country,
                    'trades_count' => (int) $r->trades_count,
                    'win_rate' => $winRate,
                    'net_pnl' => $netPnl,
                    'peak_balance' => $peak,
                    'score' => $this->score($netPnl, $winRate, (int) $r->trades_count),
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->values()
            ->take($limit)
            ->map(function ($row, $i) {
                $row['rank'] = $i + 1;

                return $row;
            });
    }

    /**
     * Composite ranking score: realised P&L weighted by consistency.
     */
    private function score(int $netPnl, float $winRate, int $trades): int
    {
        return (int) round($netPnl + ($winRate * $trades * 2));
    }

    private function since(string $period): ?Carbon
    {
        return match ($period) {
            'weekly' => now()->subDays(7),
            'monthly' => now()->subDays(30),
            default => null,
        };
    }

    /**
     * Persist a ranked snapshot for a period (used by the scheduled command + seeder).
     */
    public function snapshot(string $period): int
    {
        $rows = $this->compute($period, 1000);
        $date = now()->toDateString();
        $count = 0;

        foreach ($rows as $row) {
            LeaderboardSnapshot::updateOrCreate(
                ['user_id' => $row['user_id'], 'period' => $period, 'period_date' => $date],
                [
                    'rank' => $row['rank'],
                    'trades_count' => $row['trades_count'],
                    'win_rate' => $row['win_rate'],
                    'net_pnl' => $row['net_pnl'],
                    'peak_balance' => $row['peak_balance'],
                    'score' => $row['score'],
                    'computed_at' => now(),
                ]
            );
            $count++;
        }

        Cache::forget("leaderboard:{$period}");

        return $count;
    }

    public function forget(): void
    {
        foreach (self::PERIODS as $p) {
            Cache::forget("leaderboard:{$p}");
        }
    }
}
