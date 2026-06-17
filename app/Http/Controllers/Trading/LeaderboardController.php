<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Services\Trading\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function __construct(private readonly LeaderboardService $leaderboard) {}

    /** GET /trade/leaderboard */
    public function index(Request $request)
    {
        $period = $request->get('period', 'all_time');
        if (! in_array($period, LeaderboardService::PERIODS, true)) {
            $period = 'all_time';
        }

        $rows = $this->leaderboard->ranked($period, 100);
        $myRank = $rows->firstWhere('user_id', Auth::id());

        return view('trading.leaderboard', compact('rows', 'period', 'myRank'));
    }
}
