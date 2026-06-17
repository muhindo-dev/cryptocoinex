<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Asset;
use App\Models\Trading\Tournament;
use App\Services\Trading\TournamentService;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct(private readonly TournamentService $service) {}

    public function index()
    {
        $tournaments = Tournament::with(['asset', 'winner'])->withCount('participants')->latest()->paginate(20);

        return view('admin.trading.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        $assets = Asset::where('enabled', true)->orderBy('name')->get();

        return view('admin.trading.tournaments.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'asset_id' => ['nullable', 'exists:trading_assets,id'],
            'starting_balance' => ['required', 'integer', 'min:100', 'max:1000000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        $data['status'] = 'upcoming';

        Tournament::create($data);

        return redirect()->route('admin.trading.tournaments.index')->with('success', 'Tournament created.');
    }

    public function show(Tournament $tournament)
    {
        $standings = $this->service->standings($tournament);
        $tournament->load('asset');

        return view('admin.trading.tournaments.show', compact('tournament', 'standings'));
    }

    /** Finalize a tournament early (declare winner now). */
    public function end(Tournament $tournament)
    {
        $this->service->finalize($tournament);

        return back()->with('success', 'Tournament ended and winner declared.');
    }

    public function destroy(Tournament $tournament)
    {
        $tournament->delete();

        return redirect()->route('admin.trading.tournaments.index')->with('success', 'Tournament deleted.');
    }
}
