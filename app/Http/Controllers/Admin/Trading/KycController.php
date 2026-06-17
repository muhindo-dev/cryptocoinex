<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use App\Services\Trading\KycService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin review of identity verifications. The document photo is streamed from
 * the private disk through this admin-gated controller only.
 */
class KycController extends Controller
{
    public function __construct(private readonly KycService $kyc) {}

    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $query = KycSubmission::with(['user', 'reviewer'])->latest();
        if (in_array($status, ['pending', 'approved', 'declined', 'resubmit'], true)) {
            $query->where('status', $status);
        }

        return view('admin.trading.kyc.index', [
            'submissions' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => [
                'pending' => KycSubmission::where('status', 'pending')->count(),
                'approved' => KycSubmission::where('status', 'approved')->count(),
                'declined' => KycSubmission::where('status', 'declined')->count(),
                'resubmit' => KycSubmission::where('status', 'resubmit')->count(),
            ],
        ]);
    }

    /** Stream the private document image to the reviewing admin only. */
    public function document(KycSubmission $kyc): StreamedResponse
    {
        abort_unless($kyc->document_path && Storage::disk('local')->exists($kyc->document_path), 404);

        return Storage::disk('local')->response(
            $kyc->document_path,
            'kyc-'.$kyc->id.'-document',
            ['Cache-Control' => 'private, no-store']
        );
    }

    public function approve(Request $request, KycSubmission $kyc)
    {
        return $this->act(fn () => $this->kyc->approve($kyc, $request->user()), 'Verification approved — the member can now use live features.');
    }

    public function decline(Request $request, KycSubmission $kyc)
    {
        $note = $request->validate(['admin_note' => ['nullable', 'string', 'max:300']])['admin_note'] ?? null;

        return $this->act(fn () => $this->kyc->decline($kyc, $request->user(), $note), 'Verification declined. The member has been notified.');
    }

    public function redo(Request $request, KycSubmission $kyc)
    {
        $note = $request->validate(['admin_note' => ['nullable', 'string', 'max:300']])['admin_note'] ?? null;

        return $this->act(fn () => $this->kyc->requestRedo($kyc, $request->user(), $note), 'Asked the member to redo their verification.');
    }

    private function act(callable $fn, string $success)
    {
        try {
            $fn();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }
}
