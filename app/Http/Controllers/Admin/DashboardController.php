<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trading\Trade;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $settled = Trade::whereIn('status', ['won', 'lost'])->count();
        $wins = $settled > 0 ? Trade::where('status', 'won')->count() : 0;

        $stats = [
            'trading_total' => Trade::count(),
            'trading_open' => Trade::where('status', 'open')->count(),
            'trading_today' => Trade::whereDate('opened_at', today())->count(),
            'total_students' => User::whereHas('tradingWallet')->count(),
            'active_today' => Trade::whereDate('opened_at', today())->distinct('user_id')->count('user_id'),
            'win_rate' => $settled > 0 ? round($wins / $settled * 100) : 0,
        ];

        $recentTrades = Trade::with(['user', 'asset'])
            ->latest('opened_at')
            ->limit(10)
            ->get();

        $assetVolume = Trade::select('asset_id', DB::raw('count(*) as trade_count'), DB::raw('sum(stake) as total_stake'))
            ->with('asset')
            ->groupBy('asset_id')
            ->orderByDesc('trade_count')
            ->get();

        // ── Chart datasets (ApexCharts) ──
        $tradesPerDay = $this->tradesPerDay(30);

        $outcomeBreakdown = [
            'won' => Trade::where('status', 'won')->count(),
            'lost' => Trade::where('status', 'lost')->count(),
            'tie' => Trade::where('status', 'tie')->count(),
        ];

        $assetPopularity = $assetVolume->map(fn ($r) => [
            'label' => $r->asset?->symbol ?? 'Unknown',
            'count' => (int) $r->trade_count,
        ])->values();

        // Queue depth (pending settlement jobs) — works for the redis/database driver in use.
        try {
            $stats['queue_depth'] = \Illuminate\Support\Facades\Queue::size();
        } catch (\Throwable $e) {
            $stats['queue_depth'] = 0;
        }

        return view('admin.dashboard', compact(
            'stats', 'recentTrades', 'assetVolume', 'user',
            'tradesPerDay', 'outcomeBreakdown', 'assetPopularity'
        ));
    }

    /**
     * Trade counts per day for the last $days days, zero-filled.
     *
     * @return array{categories: array<string>, data: array<int>}
     */
    private function tradesPerDay(int $days): array
    {
        $rows = Trade::where('opened_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->select(DB::raw('DATE(opened_at) as d'), DB::raw('count(*) as c'))
            ->groupBy('d')
            ->pluck('c', 'd');

        $categories = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $categories[] = now()->subDays($i)->format('d M');
            $data[] = (int) ($rows[$date] ?? 0);
        }

        return ['categories' => $categories, 'data' => $data];
    }
}
