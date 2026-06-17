<?php

namespace App\Services\Trading;

use App\Events\Trading\TradeSettled;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettlementService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly LiveWalletService $liveWalletService,
    ) {}

    /**
     * Close an open trade right now at the live market price. The binary outcome
     * is decided by the current price vs the entry price — the trader chooses
     * when to lock it in. Idempotent via {@see settle()}.
     */
    public function closeNow(Trade $trade): Trade
    {
        if ($trade->status !== 'open') {
            return $trade;
        }

        $price = (float) MarketDataManager::for($trade->mode)->currentPrice($trade->asset);

        return $this->settle($trade, $price);
    }

    /**
     * Settle a trade at the given exit price.
     *
     * Idempotent: if the trade is already settled, this is a no-op.
     * All wallet mutations happen inside a single DB transaction.
     *
     * @throws RuntimeException on unexpected state
     */
    public function settle(Trade $trade, float $exitPrice): Trade
    {
        $justSettled = false;

        $result = DB::transaction(function () use ($trade, $exitPrice, &$justSettled) {
            // Re-fetch with lock to prevent double-settlement
            $trade = Trade::lockForUpdate()->find($trade->id);

            if ($trade->status !== 'open') {
                return $trade;
            }

            $justSettled = true;

            $outcome = $this->computeOutcome($trade, $exitPrice);
            $isLive = $trade->account === 'live';

            if ($outcome === 'won') {
                $payout = $trade->stake + (int) round($trade->stake * $trade->payout_percent / 100);
                $this->creditAccount($trade, $payout, 'won', $exitPrice);
                $trade->payout_amount = $payout;
            } elseif ($outcome === 'tie') {
                $tiePolicy = TradingSetting::get('tie_policy', 'refund');
                if ($tiePolicy === 'refund') {
                    $this->creditAccount($trade, $trade->stake, 'tie', $exitPrice);
                    $trade->payout_amount = $trade->stake;
                }
                // if policy == 'loss', stake is already held — no credit
            }
            // 'lost': stake held, no credit

            $trade->exit_price = $exitPrice;
            $trade->status = $outcome;
            $trade->settled_at = now();
            $trade->save();

            // Practice wallet keeps denormalised aggregates; the live wallet
            // maintains its own totals inside LiveWalletService.
            if (! $isLive) {
                $this->updateWalletAggregates($trade->user->tradingWallet);
            }

            return $trade->fresh();
        });

        // Side effects (achievements, leaderboard, notifications) are practice-only
        // gamification — never fire them for real-money (live) trades.
        if ($justSettled && $result->account !== 'live') {
            $result->loadMissing(['user.tradingWallet', 'asset']);
            TradeSettled::dispatch($result);
        }

        return $result;
    }

    /**
     * Credit a winning/refunded trade to the wallet backing it (practice or live).
     */
    private function creditAccount(Trade $trade, int $amount, string $outcome, float $exitPrice): void
    {
        $meta = ['outcome' => $outcome, 'entry' => $trade->entry_price, 'exit' => $exitPrice];

        if ($trade->account === 'live') {
            $wallet = $this->liveWalletService->walletFor($trade->user_id);
            $this->liveWalletService->credit(
                $wallet,
                $amount,
                $outcome === 'tie' ? 'trade_refund' : 'trade_payout',
                'Trade #'.$trade->id.' '.($outcome === 'tie' ? 'refund' : 'payout'),
                $meta,
                'trade',
                $trade->id,
            );

            return;
        }

        $this->walletService->credit(
            $trade->user->tradingWallet,
            $amount,
            $outcome === 'tie' ? 'refund' : 'payout',
            $meta,
            $trade->id,
        );
    }

    /**
     * Keep the wallet's denormalised peak/credit/debit columns in sync after a settlement.
     */
    private function updateWalletAggregates($wallet): void
    {
        if (! $wallet) {
            return;
        }
        $wallet = $wallet->fresh();
        $credited = (int) $wallet->entries()->where('amount', '>', 0)->sum('amount');
        $debited = (int) abs($wallet->entries()->where('amount', '<', 0)->sum('amount'));
        $wallet->update([
            'peak_balance' => max((int) $wallet->peak_balance, (int) $wallet->balance),
            'total_credited' => $credited,
            'total_debited' => $debited,
        ]);
    }

    /**
     * Determine outcome: 'won' | 'lost' | 'tie'
     */
    public function computeOutcome(Trade $trade, float $exitPrice): string
    {
        $entry = (float) $trade->entry_price;

        if (abs($exitPrice - $entry) < PHP_FLOAT_EPSILON * 100) {
            return 'tie';
        }

        $priceWentUp = $exitPrice > $entry;

        return match ($trade->direction) {
            'up' => $priceWentUp ? 'won' : 'lost',
            'down' => ! $priceWentUp ? 'won' : 'lost',
            default => throw new RuntimeException("Unknown direction: {$trade->direction}"),
        };
    }
}
