<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\WalletService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private readonly WalletService $walletService) {}

    public function index(Request $request)
    {
        $query = User::with('tradingWallet')->orderBy('name');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(25);

        return view('admin.trading.students.index', compact('students'));
    }

    public function show(User $student)
    {
        $wallet = $this->walletService->walletFor($student->id);
        $trades = $student->trades()->with('asset')->latest()->paginate(20);
        $entries = $wallet->entries()->latest('created_at')->limit(30)->get();

        return view('admin.trading.students.show', compact('student', 'wallet', 'trades', 'entries'));
    }

    public function topup(Request $request, User $student)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $wallet = $this->walletService->walletFor($student->id);
        $this->walletService->credit(
            $wallet,
            (int) $validated['amount'],
            'topup',
            ['reason' => $validated['reason'] ?? 'admin_topup', 'admin_id' => auth()->id()]
        );

        return back()->with('success', "Added {$validated['amount']} PRACTICE\$ to {$student->name}.");
    }

    public function reset(User $student)
    {
        $wallet = $this->walletService->walletFor($student->id);
        $startBalance = (int) TradingSetting::get('default_start_balance', 10000);

        if ($wallet->balance > 0) {
            $this->walletService->debit(
                $wallet,
                $wallet->balance,
                'reset',
                ['reason' => 'admin_reset', 'admin_id' => auth()->id()]
            );
            $wallet->refresh();
        }

        $this->walletService->credit(
            $wallet,
            $startBalance,
            'reset',
            ['reason' => 'admin_reset_to_default', 'admin_id' => auth()->id()]
        );

        return back()->with('success', "Wallet for {$student->name} reset to {$startBalance} PRACTICE\$.");
    }

    /**
     * Full account wipe — permanently delete ALL of the student's money/wallet
     * data (wallet, ledger, trades, achievements, notifications, leaderboard,
     * tournaments) and fund a fresh wallet. Irreversible.
     */
    public function wipe(User $student)
    {
        $result = $this->walletService->wipeAndReset($student);
        $d = $result['deleted'];

        return back()->with('success', sprintf(
            'Fully wiped %s — deleted %d trades, %d ledger entries, %d achievements, %d notifications. Fresh wallet funded.',
            $student->name, $d['trades'], $d['ledger_entries'], $d['achievements'], $d['notifications']
        ));
    }
}
