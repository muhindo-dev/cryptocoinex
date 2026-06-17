<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    private array $keys = [
        'default_start_balance',
        'default_mode',
        'live_mode_enabled',
        'tie_policy',
        'allow_student_reset',
    ];

    public function overview()
    {
        $totalTrades = Trade::count();
        $openTrades = Trade::where('status', 'open')->count();
        $todayTrades = Trade::whereDate('opened_at', today())->count();
        $totalStudents = User::whereHas('tradingWallet')->count();
        $activeToday = Trade::whereDate('opened_at', today())
            ->distinct('user_id')->count('user_id');

        $winRate = 0;
        $settled = Trade::whereIn('status', ['won', 'lost'])->count();
        if ($settled > 0) {
            $wins = Trade::where('status', 'won')->count();
            $winRate = round($wins / $settled * 100);
        }

        $recentTrades = Trade::with(['user', 'asset'])
            ->latest('opened_at')
            ->limit(10)
            ->get();

        $assetVolume = Trade::select('asset_id', DB::raw('count(*) as trade_count'), DB::raw('sum(stake) as total_stake'))
            ->with('asset')
            ->groupBy('asset_id')
            ->orderByDesc('trade_count')
            ->get();

        return view('admin.trading.overview', compact(
            'totalTrades', 'openTrades', 'todayTrades',
            'totalStudents', 'activeToday', 'winRate',
            'recentTrades', 'assetVolume'
        ));
    }

    public function index()
    {
        $settings = [];
        foreach ($this->keys as $key) {
            $settings[$key] = TradingSetting::get($key, '');
        }

        return view('admin.trading.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_start_balance' => ['required', 'integer', 'min:1'],
            'default_mode' => ['required', 'in:sim,live'],
            'live_mode_enabled' => ['nullable', 'boolean'],
            'tie_policy' => ['required', 'in:refund,loss'],
            'allow_student_reset' => ['nullable', 'boolean'],
        ]);

        TradingSetting::set('default_start_balance', (string) $validated['default_start_balance']);
        TradingSetting::set('default_mode', $validated['default_mode']);
        TradingSetting::set('live_mode_enabled', $request->boolean('live_mode_enabled') ? 'true' : 'false');
        TradingSetting::set('tie_policy', $validated['tie_policy']);
        TradingSetting::set('allow_student_reset', $request->boolean('allow_student_reset') ? 'true' : 'false');

        return back()->with('success', 'Trading settings saved.');
    }
}
