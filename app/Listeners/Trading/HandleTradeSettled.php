<?php

namespace App\Listeners\Trading;

use App\Events\Trading\TradeSettled;
use App\Services\Trading\AchievementService;
use App\Services\Trading\LeaderboardService;
use App\Services\Trading\NotificationService;
use Illuminate\Support\Facades\Log;

class HandleTradeSettled
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AchievementService $achievements,
        private readonly LeaderboardService $leaderboard,
    ) {}

    public function handle(TradeSettled $event): void
    {
        $trade = $event->trade;
        $user = $trade->user;

        if (! $user) {
            return;
        }

        try {
            // 1. Trade-settled notification
            $this->notifications->notify(
                $user->id,
                'trade_settled',
                $this->title($trade),
                $this->body($trade),
                route('trade.index'),
                $trade->status === 'won' ? 'fa-trophy' : ($trade->status === 'lost' ? 'fa-circle-xmark' : 'fa-rotate-left'),
                ['trade_id' => $trade->id, 'status' => $trade->status, 'payout' => $trade->payout_amount],
            );

            // 2. Low-balance warning
            $wallet = $user->tradingWallet;
            if ($wallet && $wallet->balance < 500) {
                $this->notifications->notify(
                    $user->id,
                    'balance_low',
                    'Balance running low',
                    "Your practice balance is down to {$wallet->balance} USD. You can reset it any time.",
                    route('trade.wallet.page'),
                    'fa-triangle-exclamation',
                );
            }

            // 3. Award any newly-earned achievements
            $this->achievements->evaluate($user);

            // 4. Invalidate cached leaderboards
            $this->leaderboard->forget();
        } catch (\Throwable $e) {
            // Never let post-settlement side effects break settlement.
            Log::error('HandleTradeSettled failed for trade '.$trade->id.': '.$e->getMessage());
        }
    }

    private function title($trade): string
    {
        return match ($trade->status) {
            'won' => "You won {$trade->payout_amount} USD! 🎉",
            'lost' => 'Trade lost',
            'tie' => 'Trade tied — stake returned',
            default => 'Trade settled',
        };
    }

    private function body($trade): string
    {
        $sym = $trade->asset?->symbol ?? 'asset';
        $dir = strtoupper($trade->direction);

        return "#{$trade->id} {$sym} {$dir} · stake {$trade->stake} PRACTICE\$.";
    }
}
