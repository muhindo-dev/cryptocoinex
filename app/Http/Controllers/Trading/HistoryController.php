<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoryController extends Controller
{
    /** GET /trade/history — full paginated history page with filters. */
    public function page(Request $request)
    {
        $trades = $this->filtered($request)->with('asset')->paginate(20)->withQueryString();
        $assets = Asset::orderBy('name')->get(['id', 'symbol']);

        return view('trading.history', compact('trades', 'assets'));
    }

    /** GET /trade/history/export — CSV download of the filtered history. */
    public function export(Request $request): StreamedResponse
    {
        $trades = $this->filtered($request)->with('asset')->get();
        $filename = 'cryptocoinex-history-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($trades) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Date', 'Asset', 'Direction', 'Mode', 'Stake', 'Entry', 'Exit', 'Status', 'Payout', 'P&L', 'Duration(s)']);
            foreach ($trades as $t) {
                $pnl = ((int) ($t->payout_amount ?? 0)) - (int) $t->stake;
                fputcsv($out, [
                    $t->id,
                    $t->settled_at?->format('Y-m-d H:i:s'),
                    $t->asset?->symbol,
                    strtoupper($t->direction),
                    strtoupper($t->mode),
                    $t->stake,
                    $t->entry_price,
                    $t->exit_price,
                    $t->status,
                    $t->payout_amount,
                    $pnl,
                    $t->expiry_seconds,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Build the filtered settled-trades query for the current user.
     */
    private function filtered(Request $request)
    {
        return Trade::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['won', 'lost', 'tie', 'void'])
            ->when($request->filled('asset'), fn ($q) => $q->where('asset_id', $request->integer('asset')))
            ->when($request->filled('direction') && in_array($request->get('direction'), ['up', 'down']),
                fn ($q) => $q->where('direction', $request->get('direction')))
            ->when($request->filled('status') && in_array($request->get('status'), ['won', 'lost', 'tie']),
                fn ($q) => $q->where('status', $request->get('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('settled_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('settled_at', '<=', $request->date('to')))
            ->latest('settled_at');
    }
}
