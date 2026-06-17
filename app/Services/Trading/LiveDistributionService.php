<?php

namespace App\Services\Trading;

use App\Models\Trading\LiveDistribution;
use App\Models\Trading\LiveDistributionShare;
use App\Models\Trading\LiveWallet;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Profit distributions: split an admin-set pool amount across every member who
 * holds a live balance, in proportion to their share of the total live balance,
 * and credit each one. Splitting uses the largest-remainder (Hamilton) method so
 * the per-member amounts always sum to EXACTLY the pool — never a unit off.
 */
class LiveDistributionService
{
    public function __construct(
        private readonly LiveWalletService $wallets,
        private readonly NotificationService $notifications,
    ) {}

    /** Wallets eligible to receive a share: members with a positive live balance. */
    public function eligibleWallets(): Collection
    {
        return LiveWallet::with('user')->where('balance', '>', 0)->orderByDesc('balance')->get();
    }

    /**
     * Preview how a pool of {$totalAmount} would split — without persisting.
     *
     * @return array{total_base:int, currency:string, rows:array<int,array{wallet:LiveWallet,base:int,pct:float,amount:int}>}
     */
    public function preview(int $totalAmount): array
    {
        return $this->computeShares($this->eligibleWallets(), max(0, $totalAmount));
    }

    /**
     * Exact integer apportionment of {$totalAmount} across the wallets, weighted
     * by balance. Floors each share, then hands the leftover units to the largest
     * fractional remainders so the total is exact.
     */
    private function computeShares(Collection $wallets, int $totalAmount): array
    {
        $totalBase = (int) $wallets->sum('balance');
        $currency = $this->wallets->currency();

        if ($totalBase <= 0 || $totalAmount <= 0 || $wallets->isEmpty()) {
            return ['total_base' => $totalBase, 'currency' => $currency, 'rows' => []];
        }

        $rows = [];
        $allocated = 0;
        foreach ($wallets as $w) {
            $exact = $totalAmount * $w->balance / $totalBase;
            $floor = (int) floor($exact);
            $rows[] = [
                'wallet' => $w,
                'base' => (int) $w->balance,
                'pct' => round($w->balance / $totalBase * 100, 4),
                'amount' => $floor,
                'frac' => $exact - $floor,
            ];
            $allocated += $floor;
        }

        // Hand the leftover units (always < member count) to the largest remainders.
        $leftover = $totalAmount - $allocated;
        if ($leftover > 0) {
            usort($rows, fn ($a, $b) => $b['frac'] <=> $a['frac']);
            for ($i = 0; $i < $leftover; $i++) {
                $rows[$i]['amount'] += 1;
            }
        }
        // Present largest-balance members first.
        usort($rows, fn ($a, $b) => $b['base'] <=> $a['base']);

        return ['total_base' => $totalBase, 'currency' => $currency, 'rows' => $rows];
    }

    /**
     * Execute a distribution: credit every eligible member their share and record
     * an immutable per-member breakdown. Atomic — either everyone is paid exactly
     * or nothing happens.
     */
    public function distribute(int $totalAmount, User $admin, ?string $note = null): LiveDistribution
    {
        if ($totalAmount <= 0) {
            throw new RuntimeException('Distribution amount must be greater than zero.');
        }

        return DB::transaction(function () use ($totalAmount, $admin, $note) {
            // Lock the eligible wallets so balances can't shift mid-distribution.
            $wallets = LiveWallet::with('user')
                ->where('balance', '>', 0)
                ->orderByDesc('balance')
                ->lockForUpdate()
                ->get();

            $totalBase = (int) $wallets->sum('balance');
            if ($wallets->isEmpty() || $totalBase <= 0) {
                throw new RuntimeException('There are no members with a live balance to distribute to.');
            }

            $computed = $this->computeShares($wallets, $totalAmount);

            $distribution = LiveDistribution::create([
                'total_amount' => $totalAmount,
                'total_base' => $totalBase,
                'members_count' => $wallets->count(),
                'currency' => $computed['currency'],
                'note' => $note,
                'created_by' => $admin->id,
            ]);

            foreach ($computed['rows'] as $row) {
                if ($row['amount'] <= 0) {
                    continue; // a member whose share rounded to zero gets no transaction
                }

                $pctLabel = rtrim(rtrim(number_format($row['pct'], 2), '0'), '.');
                $tx = $this->wallets->credit(
                    $row['wallet'],
                    $row['amount'],
                    'distribution',
                    'Profit distribution #'.$distribution->id.' — your '.$pctLabel.'% share'.($note ? ' · '.$note : ''),
                    ['distribution_id' => $distribution->id, 'percentage' => $row['pct']],
                    'distribution',
                    $distribution->id,
                );

                LiveDistributionShare::create([
                    'live_distribution_id' => $distribution->id,
                    'user_id' => $row['wallet']->user_id,
                    'live_wallet_id' => $row['wallet']->id,
                    'base_balance' => $row['base'],
                    'percentage' => $row['pct'],
                    'amount' => $row['amount'],
                    'live_transaction_id' => $tx->id,
                    'created_at' => now(),
                ]);

                $this->notifications->notify(
                    $row['wallet']->user_id,
                    'live_profit',
                    'Profit share received 🎉',
                    'You received '.\App\Support\Money::format($row['amount'], $computed['currency']).
                        ' ('.$pctLabel.'% share) from a profit distribution.',
                    route('trade.live'),
                    'fa-hand-holding-dollar',
                );
            }

            return $distribution->fresh();
        });
    }
}
