<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\LiveDistribution;
use App\Services\Trading\LiveDistributionService;
use App\Services\Trading\LiveWalletService;
use Illuminate\Http\Request;

/**
 * Admin profit distributions: initiate a payout pool that is split across live
 * members by balance, and follow up on every member's share.
 */
class LiveDistributionController extends Controller
{
    public function __construct(
        private readonly LiveDistributionService $distributions,
        private readonly LiveWalletService $wallets,
    ) {}

    public function index()
    {
        return view('admin.trading.live.distributions.index', [
            'distributions' => LiveDistribution::withCount('shares')->with('creator')->latest()->paginate(20),
            'currency' => $this->wallets->currency(),
        ]);
    }

    /** Create form — shows who is eligible and their current balance share. */
    public function create()
    {
        $wallets = $this->distributions->eligibleWallets();

        return view('admin.trading.live.distributions.create', [
            'wallets' => $wallets,
            'totalBase' => (int) $wallets->sum('balance'),
            'currency' => $this->wallets->currency(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'total_amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'note' => ['nullable', 'string', 'max:300'],
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Please confirm the distribution before submitting.',
        ]);

        try {
            $distribution = $this->distributions->distribute(
                (int) $data['total_amount'],
                $request->user(),
                $data['note'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.trading.live.distributions.show', $distribution)
            ->with('success', 'Distribution completed — '.$distribution->members_count.' members credited.');
    }

    /** Per-member breakdown for follow-up. */
    public function show(LiveDistribution $distribution)
    {
        $distribution->load('creator');
        $shares = $distribution->shares()->with(['user', 'transaction'])->orderByDesc('amount')->paginate(50);

        return view('admin.trading.live.distributions.show', [
            'distribution' => $distribution,
            'shares' => $shares,
            'currency' => $distribution->currency,
        ]);
    }
}
