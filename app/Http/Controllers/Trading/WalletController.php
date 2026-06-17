<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\TradingSetting;
use App\Services\Trading\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService) {}

    public function show(): JsonResponse
    {
        $wallet = $this->walletService->walletFor(Auth::id());

        return response()->json([
            'balance' => $wallet->balance,
            'currency_label' => $wallet->currency_label,
        ]);
    }

    public function ledger(Request $request): JsonResponse
    {
        $wallet = $this->walletService->walletFor(Auth::id());

        $entries = $wallet->entries()
            ->with('trade')
            ->latest('created_at')
            ->paginate(30);

        return response()->json($entries);
    }

    /** HTML wallet/ledger page for students. */
    public function page(Request $request)
    {
        $user = Auth::user();
        $wallet = $this->walletService->walletFor($user->id);

        $entries = $wallet->entries()
            ->with('trade.asset')
            ->latest('created_at')
            ->paginate(40);

        $allowReset = TradingSetting::get('allow_student_reset', 'false') === 'true';

        return view('trading.wallet', compact('wallet', 'entries', 'allowReset'));
    }

    /** Student self-reset (only if allow_student_reset = true). */
    public function reset(Request $request): RedirectResponse|JsonResponse
    {
        if (TradingSetting::get('allow_student_reset', 'false') !== 'true') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Self-reset is not enabled.'], 403);
            }
            abort(403, 'Self-reset is not enabled.');
        }

        $user = Auth::user();
        $wallet = $this->walletService->walletFor($user->id);
        $startBalance = (int) TradingSetting::get('default_start_balance', 1000);

        if ($wallet->balance > 0) {
            $this->walletService->debit($wallet, $wallet->balance, 'reset', [
                'reason' => 'student_self_reset',
            ]);
            $wallet->refresh();
        }

        $this->walletService->credit($wallet, $startBalance, 'reset', [
            'reason' => 'student_self_reset_to_default',
        ]);

        $wallet->increment('resets_count');

        if ($request->wantsJson()) {
            return response()->json(['balance' => $startBalance, 'resets_count' => $wallet->resets_count]);
        }

        return back()->with('success', "Wallet reset to {$startBalance} PRACTICE\$.");
    }

    /**
     * Full account wipe — permanently delete ALL of the student's money/wallet
     * data and start fresh. Gated by the same allow_student_reset setting.
     */
    public function wipe(Request $request): RedirectResponse|JsonResponse
    {
        if (TradingSetting::get('allow_student_reset', 'false') !== 'true') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Account reset is not enabled.'], 403);
            }
            abort(403, 'Account reset is not enabled.');
        }

        $result = $this->walletService->wipeAndReset(Auth::user());

        if ($request->wantsJson()) {
            return response()->json([
                'balance' => $result['wallet']->balance,
                'deleted' => $result['deleted'],
            ]);
        }

        return back()->with('success', 'Your account was fully reset — all trades, ledger history and stats were deleted, and your balance restored to the starting amount.');
    }
}
