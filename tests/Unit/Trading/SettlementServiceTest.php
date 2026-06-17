<?php

namespace Tests\Unit\Trading;

use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Models\Trading\Wallet;
use App\Models\User;
use App\Services\Trading\SettlementService;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettlementService $service;

    private WalletService $walletService;

    private User $user;

    private Asset $asset;

    private Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = new WalletService;
        $this->service = new SettlementService($this->walletService, app(\App\Services\Trading\LiveWalletService::class));

        $this->user = User::factory()->create();
        $this->wallet = $this->walletService->grantStartingBalance($this->user->id);

        TradingSetting::set('default_start_balance', '10000');
        TradingSetting::set('tie_policy', 'refund');

        $this->asset = Asset::create([
            'symbol' => 'TEST-SIM',
            'name' => 'Test Asset',
            'asset_class' => 'sim',
            'payout_percent' => 80.00,
            'min_stake' => 1,
            'max_stake' => 1000,
            'allowed_expiries' => [30, 60],
            'supports_live' => false,
            'sim_start_price' => 100,
            'sim_drift' => 0,
            'sim_volatility' => 0.01,
            'sim_seed' => 999,
            'enabled' => true,
        ]);
    }

    // ── computeOutcome — UP direction ─────────────────────────────────────────

    public function test_up_wins_when_price_rises(): void
    {
        $trade = $this->makeTrade('up', 100.00);
        $this->assertEquals('won', $this->service->computeOutcome($trade, 101.00));
    }

    public function test_up_loses_when_price_falls(): void
    {
        $trade = $this->makeTrade('up', 100.00);
        $this->assertEquals('lost', $this->service->computeOutcome($trade, 99.00));
    }

    public function test_up_ties_when_price_equal(): void
    {
        $trade = $this->makeTrade('up', 100.00);
        $this->assertEquals('tie', $this->service->computeOutcome($trade, 100.00));
    }

    // ── computeOutcome — DOWN direction ───────────────────────────────────────

    public function test_down_wins_when_price_falls(): void
    {
        $trade = $this->makeTrade('down', 100.00);
        $this->assertEquals('won', $this->service->computeOutcome($trade, 99.00));
    }

    public function test_down_loses_when_price_rises(): void
    {
        $trade = $this->makeTrade('down', 100.00);
        $this->assertEquals('lost', $this->service->computeOutcome($trade, 101.00));
    }

    public function test_down_ties_when_price_equal(): void
    {
        $trade = $this->makeTrade('down', 100.00);
        $this->assertEquals('tie', $this->service->computeOutcome($trade, 100.00));
    }

    // ── settle — payout correctness ───────────────────────────────────────────

    public function test_win_credits_stake_plus_payout_percent(): void
    {
        $trade = $this->makePersistedTrade('up', 1000, 100.00, 80.00);
        $balanceBefore = $this->wallet->fresh()->balance;

        $settled = $this->service->settle($trade, 101.00);

        $this->assertEquals('won', $settled->status);
        $this->assertEquals(1800, $settled->payout_amount); // 1000 + 80%
        $this->assertEquals($balanceBefore + 1800, $this->wallet->fresh()->balance);
    }

    public function test_loss_credits_nothing(): void
    {
        $trade = $this->makePersistedTrade('up', 1000, 100.00, 80.00);
        $balanceBefore = $this->wallet->fresh()->balance;

        $settled = $this->service->settle($trade, 99.00);

        $this->assertEquals('lost', $settled->status);
        $this->assertNull($settled->payout_amount);
        $this->assertEquals($balanceBefore, $this->wallet->fresh()->balance);
    }

    public function test_tie_refunds_stake_when_policy_is_refund(): void
    {
        TradingSetting::set('tie_policy', 'refund');
        $trade = $this->makePersistedTrade('up', 500, 100.00, 80.00);
        $balanceBefore = $this->wallet->fresh()->balance;

        $settled = $this->service->settle($trade, 100.00);

        $this->assertEquals('tie', $settled->status);
        $this->assertEquals(500, $settled->payout_amount);
        $this->assertEquals($balanceBefore + 500, $this->wallet->fresh()->balance);
    }

    public function test_tie_credits_nothing_when_policy_is_loss(): void
    {
        TradingSetting::set('tie_policy', 'loss');
        $trade = $this->makePersistedTrade('up', 500, 100.00, 80.00);
        $balanceBefore = $this->wallet->fresh()->balance;

        $settled = $this->service->settle($trade, 100.00);

        $this->assertEquals('tie', $settled->status);
        $this->assertEquals($balanceBefore, $this->wallet->fresh()->balance);
    }

    // ── settle — idempotency ──────────────────────────────────────────────────

    public function test_settle_is_idempotent_on_already_settled_trade(): void
    {
        $trade = $this->makePersistedTrade('up', 200, 100.00, 80.00);
        $this->service->settle($trade, 101.00);
        $balanceAfterFirst = $this->wallet->fresh()->balance;

        // Call again — must be no-op
        $this->service->settle($trade, 101.00);

        $this->assertEquals($balanceAfterFirst, $this->wallet->fresh()->balance);
        $this->assertEquals(1, $trade->walletEntries()->where('type', 'payout')->count());
    }

    public function test_settle_records_exit_price_and_settled_at(): void
    {
        $trade = $this->makePersistedTrade('down', 100, 200.00, 80.00);
        $settled = $this->service->settle($trade, 195.00);

        $this->assertEquals(195.00, (float) $settled->exit_price);
        $this->assertNotNull($settled->settled_at);
    }

    // ── ledger integrity after settlement ─────────────────────────────────────

    public function test_ledger_sum_equals_balance_after_win(): void
    {
        $trade = $this->makePersistedTrade('up', 300, 100.00, 80.00);
        $this->service->settle($trade, 110.00);

        $wallet = $this->wallet->fresh();
        $this->assertTrue($this->walletService->verifyLedgerConsistency($wallet));
    }

    public function test_ledger_sum_equals_balance_after_loss(): void
    {
        $trade = $this->makePersistedTrade('up', 300, 100.00, 80.00);
        $this->service->settle($trade, 90.00);

        $wallet = $this->wallet->fresh();
        $this->assertTrue($this->walletService->verifyLedgerConsistency($wallet));
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeTrade(string $direction, float $entryPrice): Trade
    {
        $trade = new Trade;
        $trade->direction = $direction;
        $trade->entry_price = $entryPrice;
        $trade->stake = 100;
        $trade->payout_percent = 80.00;
        $trade->status = 'open';

        return $trade;
    }

    private function makePersistedTrade(
        string $direction,
        int $stake,
        float $entryPrice,
        float $payoutPercent
    ): Trade {
        // Debit the stake from the wallet first (as TradeService would do)
        $wallet = $this->wallet->fresh();
        $trade = Trade::create([
            'user_id' => $this->user->id,
            'asset_id' => $this->asset->id,
            'mode' => 'sim',
            'direction' => $direction,
            'stake' => $stake,
            'payout_percent' => $payoutPercent,
            'entry_price' => $entryPrice,
            'opened_at' => now(),
            'expires_at' => now()->addSeconds(60),
            'expiry_seconds' => 60,
            'status' => 'open',
        ]);

        $this->walletService->debit($wallet, $stake, 'stake_hold', [], $trade->id);
        $this->wallet->refresh();

        return $trade;
    }
}
