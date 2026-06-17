<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Services\Trading\KycService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Student-facing identity verification. Documents are stored on the private disk
 * and never served to the public.
 */
class KycController extends Controller
{
    public function __construct(private readonly KycService $kyc) {}

    /** GET /trade/kyc — verification status + submission form. */
    public function show()
    {
        $user = Auth::user();

        return view('trading.kyc', [
            'user' => $user,
            'submission' => $user->latestKyc(),
            'canSubmit' => $user->canSubmitKyc(),
        ]);
    }

    /** POST /trade/kyc — file a verification attempt. */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->canSubmitKyc()) {
            return redirect()->route('trade.kyc')
                ->with('error', 'You already have a verification in progress.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'document_type' => ['required', Rule::in(['passport', 'national_id', 'drivers_license', 'other'])],
            'document_number' => ['required', 'string', 'max:80'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'], // 8 MB
            'message' => ['nullable', 'string', 'max:500'],
        ], [], ['document' => 'document photo']);

        // Private disk — ID documents must never be web-accessible.
        $path = $request->file('document')->store('kyc_documents', 'local');

        $this->kyc->submit($user, $data, $path);

        return redirect()->route('trade.kyc')
            ->with('success', 'Verification submitted. Our team will review it shortly — you\'ll be notified of the outcome.');
    }
}
