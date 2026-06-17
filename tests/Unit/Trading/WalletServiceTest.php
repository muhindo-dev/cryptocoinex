<?php

namespace Tests\Unit\Trading;

use App\Models\Trading\TradingSetting;
use App\Models\Trading\Wallet;
use App\Models\Trading\WalletEntry;
use App\Models\User;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WalletService;
        $this->user = User::factory()->create();
    }

    // ── walletFor ──────────────────────────────────────────────────────────

    public function test_wallet_for_creates_wallet_with_zero_balance(): void
    {
        $wallet = $this->service->walletFor($this->user->id);

        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals('USD', $wallet->currency_label);
    }

    public function test_wallet_for_returns_existing_wallet(): void
    {
        $w1 = $this->service->walletFor($this->user->id);
        $w2 = $this->service->walletFor($this->user->id);

        $this->assertEquals($w1->id, $w2->id);
        $this->assertEquals(1, Wallet::count());
    }

    // ── grantStartingBalance ────────────────────────────────────────────────

    public function test_grant_starting_balance_credits_configured_amount(): void
    {
        TradingSetting::set('default_start_balance', '10000');

        $wallet = $this->service->grantStartingBalance($this->user->id);

        $this->assertEquals(10000, $wallet->balance);
        $this->assertEquals(1, $wallet->entries()->count());
        $this->assertEquals(10000, $wallet->entries()->first()->amount);
        $this->assertEquals('topup', $wallet->entries()->first()->type);
    }

    public function test_grant_starting_balance_is_idempotent(): void
    {
        TradingSetting::set('default_start_balance', '10000');

        $this->service->grantStartingBalance($this->user->id);
        $wallet = $this->service->grantStartingBalance($this->user->id);

        $this->assertEquals(10000, $wallet->balance);
        $this->assertEquals(1, $wallet->entries()->count());
    }

    // ── credit ─────────────────────────────────────────────────────────────

    public function test_credit_increases_balance_and_creates_ledger_entry(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 500, 'topup');

        $this->assertEquals(500, $wallet->balance);

        $entry = WalletEntry::first();
        $this->assertEquals(500, $entry->amount);
        $this->assertEquals(500, $entry->balance_after);
        $this->assertEquals('topup', $entry->type);
    }

    public function test_multiple_credits_accumulate_correctly(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 1000, 'topup');
        $wallet = $this->service->credit($wallet, 250, 'payout');

        $this->assertEquals(1250, $wallet->balance);
        $this->assertEquals(2, $wallet->entries()->count());
    }

    public function test_credit_rejects_zero_amount(): void
    {
        $this->expectException(RuntimeException::class);

        $wallet = $this->service->walletFor($this->user->id);
        $this->service->credit($wallet, 0, 'topup');
    }

    public function test_credit_rejects_negative_amount(): void
    {
        $this->expectException(RuntimeException::class);

        $wallet = $this->service->walletFor($this->user->id);
        $this->service->credit($wallet, -100, 'topup');
    }

    // ── debit ──────────────────────────────────────────────────────────────

    public function test_debit_decreases_balance_and_creates_ledger_entry(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 1000, 'topup');
        $wallet = $this->service->debit($wallet, 300, 'stake_hold');

        $this->assertEquals(700, $wallet->balance);

        $debitEntry = $wallet->entries()->orderByDesc('id')->first();
        $this->assertEquals(-300, $debitEntry->amount);
        $this->assertEquals(700, $debitEntry->balance_after);
        $this->assertEquals('stake_hold', $debitEntry->type);
    }

    public function test_debit_rejects_overdraft(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/insufficient balance/i');

        $wallet = $this->service->walletFor($this->user->id);
        $this->service->credit($wallet, 100, 'topup');
        $this->service->debit($wallet->fresh(), 200, 'stake_hold');
    }

    public function test_debit_allows_exact_balance(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 500, 'topup');
        $wallet = $this->service->debit($wallet, 500, 'stake_hold');

        $this->assertEquals(0, $wallet->balance);
    }

    public function test_debit_rejects_zero_amount(): void
    {
        $this->expectException(RuntimeException::class);

        $wallet = $this->service->walletFor($this->user->id);
        $this->service->debit($wallet, 0, 'stake_hold');
    }

    // ── ledger invariants ──────────────────────────────────────────────────

    public function test_ledger_sum_equals_balance_after_multiple_operations(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 10000, 'topup');
        $wallet = $this->service->debit($wallet, 200, 'stake_hold');
        $wallet = $this->service->credit($wallet, 360, 'payout');
        $wallet = $this->service->debit($wallet, 500, 'stake_hold');

        $ledgerSum = (int) $wallet->entries()->sum('amount');
        $this->assertEquals($ledgerSum, $wallet->balance);
        $this->assertTrue($this->service->verifyLedgerConsistency($wallet));
    }

    public function test_balance_never_goes_negative(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 100, 'topup');

        try {
            $this->service->debit($wallet, 150, 'stake_hold');
        } catch (RuntimeException) {
            // expected
        }

        $this->assertGreaterThanOrEqual(0, $wallet->fresh()->balance);
    }

    public function test_debit_is_atomic_on_failure(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 100, 'topup');

        $balanceBefore = $wallet->balance;
        $entriesBefore = $wallet->entries()->count();

        try {
            $this->service->debit($wallet, 999, 'stake_hold');
        } catch (RuntimeException) {
            // expected
        }

        $wallet->refresh();
        $this->assertEquals($balanceBefore, $wallet->balance);
        $this->assertEquals($entriesBefore, $wallet->entries()->count());
    }

    // ── verifyLedgerConsistency ────────────────────────────────────────────

    public function test_verify_ledger_consistency_returns_true_for_healthy_wallet(): void
    {
        $wallet = $this->service->walletFor($this->user->id);
        $wallet = $this->service->credit($wallet, 5000, 'topup');
        $wallet = $this->service->debit($wallet, 1000, 'stake_hold');

        $this->assertTrue($this->service->verifyLedgerConsistency($wallet));
    }
}
