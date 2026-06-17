<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Tournament;
use App\Services\Trading\TournamentService;
use Illuminate\Support\Facades\Auth;

class TournamentController extends Controller
{
    public function __construct(private readonly TournamentService $service) {}

    /** GET /trade/tournaments */
    public function index()
    {
        $tournaments = Tournament::with('asset')->withCount('participants')
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'upcoming' THEN 1 ELSE 2 END")
            ->orderByDesc('starts_at')
            ->paginate(20);

        $joinedIds = \App\Models\Trading\TournamentParticipant::where('user_id', Auth::id())
            ->pluck('tournament_id')->all();

        return view('trading.tournaments.index', compact('tournaments', 'joinedIds'));
    }

    /** GET /trade/tournaments/{tournament} */
    public function show(Tournament $tournament)
    {
        $standings = $this->service->standings($tournament);
        $joined = $tournament->participants()->where('user_id', Auth::id())->exists();
        $myRow = $standings->firstWhere('user_id', Auth::id());
        $tournament->load('asset', 'winner');

        return view('trading.tournaments.show', compact('tournament', 'standings', 'joined', 'myRow'));
    }

    /** POST /trade/tournaments/{tournament}/join */
    public function join(Tournament $tournament)
    {
        if (! $tournament->isJoinable()) {
            return back()->with('error', 'This tournament is no longer open to join.');
        }
        $this->service->join($tournament, Auth::user());

        return back()->with('success', "You joined {$tournament->name}!");
    }
}
