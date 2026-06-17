<?php

namespace App\Services\Trading;

use App\Jobs\Trading\SettleTradeJob;
use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TradeService
{
    /**
     * Open positions have no countdown — the trader closes them manually. We
     * still set a far backstop expiry so an abandoned position eventually
     * auto-settles (the SettleTradeJob is a safety net, not the main path).
     */
    public const DEFAULT_HOLD_SECONDS = 604800; // 7 days

    public function __construct(
        private readonly WalletService $walletService,
        private readonly LiveWalletService $liveWalletService,
    ) {}

    /**
     * Open a new trade for the given user.
     *
     * Validates stake vs. asset limits and wallet balance, locks entry price,
     * debits the stake, persists the trade, and dispatches SettleTradeJob.
     *
     * @throws RuntimeException on validation failure
     */
    public function open(
        User $user,
        Asset $asset,
        string $mode,
        string $direction,
        int $stake,
        ?int $expirySeconds = null,
        string $account = 'demo',
    ): Trade {
        $expirySeconds = $expirySeconds ?: self::DEFAULT_HOLD_SECONDS;

        if (! in_array($account, ['demo', 'live'], true)) {
            throw new RuntimeException("Invalid account: {$account}");
        }

        // Identity verification is required before any buying or selling.
        if ($user->requiresKyc()) {
            throw new RuntimeException('Verify your identity to start trading.');
        }

        $this->validateAsset($asset, $mode);
        $this->validateDirection($direction);
        $this->validateStake($asset, $stake);

        if ($account === 'live') {
            return $this->openLive($user, $asset, $mode, $direction, $stake, $expirySeconds);
        }

        return DB::transaction(function () use ($user, $asset, $mode, $direction, $stake, $expirySeconds) {
            $wallet = $this->walletService->walletFor($user->id);

            if ($wallet->balance < $stake) {
                throw new RuntimeException('Insufficient balance.');
            }

            $driver = MarketDataManager::for($mode);
            $entryPrice = $driver->currentPrice($asset);

            $openedAt = now();
            $expiresAt = $openedAt->copy()->addSeconds($expirySeconds);

            $trade = Trade::create([
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'mode' => $mode,
                'account' => 'demo',
                'direction' => $direction,
                'stake' => $stake,
                'payout_percent' => $asset->payout_percent,
                'entry_price' => $entryPrice,
                'opened_at' => $openedAt,
                'expires_at' => $expiresAt,
                'expiry_seconds' => $expirySeconds,
                'status' => 'open',
            ]);

            $this->walletService->debit(
                $wallet,
                $stake,
                'stake_hold',
                ['trade_id' => $trade->id],
                $trade->id
            );

            SettleTradeJob::dispatch($trade->id)->delay($expiresAt);

            return $trade->fresh();
        });
    }

    /**
     * Open a REAL-MONEY trade against the user's Live Account wallet. The stake
     * is debited from live funds; settlement credits the live wallet too.
     */
    private function openLive(
        User $user,
        Asset $asset,
        string $mode,
        string $direction,
        int $stake,
        int $expirySeconds,
    ): Trade {
        if (TradingSetting::get('live_account_enabled', 'true') !== 'true') {
            throw new RuntimeException('Live trading is currently unavailable.');
        }

        return DB::transaction(function () use ($user, $asset, $mode, $direction, $stake, $expirySeconds) {
            $wallet = $this->liveWalletService->walletFor($user->id);

            // Available balance reserves any funds tied up in pending withdrawals.
            if ($this->liveWalletService->availableBalance($wallet) < $stake) {
                throw new RuntimeException('Insufficient Live Account balance.');
            }

            $entryPrice = MarketDataManager::for($mode)->currentPrice($asset);
            $openedAt = now();
            $expiresAt = $openedAt->copy()->addSeconds($expirySeconds);

            $trade = Trade::create([
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'mode' => $mode,
                'account' => 'live',
                'direction' => $direction,
                'stake' => $stake,
                'payout_percent' => $asset->payout_percent,
                'entry_price' => $entryPrice,
                'opened_at' => $openedAt,
                'expires_at' => $expiresAt,
                'expiry_seconds' => $expirySeconds,
                'status' => 'open',
            ]);

            $this->liveWalletService->debit(
                $wallet,
                $stake,
                'trade_stake',
                'Trade #'.$trade->id.' stake ('.strtoupper($direction).' '.$asset->symbol.')',
                ['trade_id' => $trade->id, 'direction' => $direction],
                'trade',
                $trade->id,
            );

            SettleTradeJob::dispatch($trade->id)->delay($expiresAt);

            return $trade->fresh();
        });
    }

    private function validateAsset(Asset $asset, string $mode): void
    {
        if (! $asset->enabled) {
            throw new RuntimeException('Asset is not available for trading.');
        }
        if ($mode === 'live' && ! $asset->supports_live) {
            throw new RuntimeException('This asset does not support live mode.');
        }
    }

    private function validateDirection(string $direction): void
    {
        if (! in_array($direction, ['up', 'down'], true)) {
            throw new RuntimeException("Invalid direction: {$direction}");
        }
    }

    private function validateExpiry(Asset $asset, int $expirySeconds): void
    {
        $allowed = $asset->allowed_expiries ?? [30, 60, 300];
        if (! in_array($expirySeconds, $allowed, true)) {
            throw new RuntimeException("Expiry {$expirySeconds}s is not allowed for this asset.");
        }
    }

    private function validateStake(Asset $asset, int $stake): void
    {
        if ($stake < $asset->min_stake) {
            throw new RuntimeException("Minimum stake is {$asset->min_stake}.");
        }
        if ($stake > $asset->max_stake) {
            throw new RuntimeException("Maximum stake is {$asset->max_stake}.");
        }
    }
}
