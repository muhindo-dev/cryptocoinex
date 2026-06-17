<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    /** GET /trade/journal — annotated trades grouped by date. */
    public function page(Request $request)
    {
        $query = Trade::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['won', 'lost', 'tie'])
            ->where(function ($q) {
                $q->whereNotNull('notes')->orWhereNotNull('tags');
            })
            ->with('asset')
            ->latest('settled_at');

        if ($request->filled('tag')) {
            $tag = $request->get('tag');
            $query->whereJsonContains('tags', $tag);
        }

        $trades = $query->paginate(20)->withQueryString();

        // Collect distinct tags for the filter bar
        $allTags = Trade::where('user_id', Auth::id())
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->values();

        $grouped = $trades->getCollection()->groupBy(fn ($t) => $t->settled_at?->format('Y-m-d') ?? 'Unknown');

        return view('trading.journal', compact('trades', 'grouped', 'allTags'));
    }

    /** POST /trade/{trade}/note — save/update a note on a settled trade. */
    public function saveNote(Request $request, Trade $trade): JsonResponse
    {
        abort_unless($trade->user_id === Auth::id(), 403);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
            'sentiment' => ['nullable', 'in:confident,unsure,fomo'],
        ]);

        $trade->update([
            'notes' => $data['notes'] ?? null,
            'tags' => $data['tags'] ?? null,
            'sentiment' => $data['sentiment'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }
}
