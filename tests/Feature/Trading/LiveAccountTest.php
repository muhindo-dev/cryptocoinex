<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\DepositRequest;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\LiveWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LiveAccountTest extends TestCase
{
    use RefreshDatabase;

    private LiveWalletService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->svc = app(LiveWalletService::class);
        TradingSetting::set('live_account_enabled', 'true');
        TradingSetting::set('live_account_currency', 'USD');
        TradingSetting::set('live_account_min_deposit', '10');
        TradingSetting::set('live_account_min_withdrawal', '20');
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student', 'kyc_status' => 'approved']);
    }

    // ── Ledger primitives ────────────────────────────────────────────────────

    public function test_credit_and_debit_keep_balance_and_ledger_in_sync(): void
    {
        $wallet = $this->svc->walletFor($this->student()->id);

        $this->svc->credit($wallet, 100000, 'deposit', 'test');
        $this->svc->debit($wallet->fresh(), 30000, 'withdrawal', 'test');

        $wallet->refresh();
        $this->assertSame(70000, $wallet->balance);
        $this->assertSame(100000, $wallet->total_deposited);
        $this->assertSame(30000, $wallet->total_withdrawn);
        $this->assertTrue($this->svc->verifyLedger($wallet));
    }

    public function test_debit_cannot_overdraw(): void
    {
        $wallet = $this->svc->walletFor($this->student()->id);
        $this->svc->credit($wallet, 5000, 'deposit', 'test');

        $this->expectException(\RuntimeException::class);
        $this->svc->debit($wallet->fresh(), 6000, 'withdrawal', 'test');
    }

    // ── Deposit lifecycle ────────────────────────────────────────────────────

    public function test_approving_a_deposit_credits_the_wallet_once(): void
    {
        $user = $this->student();
        $admin = User::factory()->create(['role' => 'admin']);
        $req = $this->svc->requestDeposit($user, 250000, 'REF123', null, null);

        $this->svc->approveDeposit($req, $admin);

        $wallet = $this->svc->walletFor($user->id);
        $this->assertSame(250000, $wallet->balance);
        $this->assertSame('approved', $req->fresh()->status);
        $this->assertNotNull($req->fresh()->live_transaction_id);

        // Double approval is rejected — no double credit.
        $this->expectException(\RuntimeException::class);
        $this->svc->approveDeposit($req->fresh(), $admin);
    }

    public function test_declining_a_deposit_does_not_credit(): void
    {
        $user = $this->student();
        $admin = User::factory()->create(['role' => 'admin']);
        $req = $this->svc->requestDeposit($user, 250000, 'REF123', null, null);

        $this->svc->declineDeposit($req, $admin, 'No payment found');

        $this->assertSame(0, $this->svc->walletFor($user->id)->balance);
        $this->assertSame('declined', $req->fresh()->status);
    }

    // ── Withdrawal lifecycle ─────────────────────────────────────────────────

    public function test_withdrawal_request_cannot_exceed_available_balance(): void
    {
        $user = $this->student();
        $wallet = $this->svc->walletFor($user->id);
        $this->svc->credit($wallet, 50000, 'deposit', 'seed');

        $this->expectException(\RuntimeException::class);
        $this->svc->requestWithdrawal($user, 60000, '+256700000000', null, null);
    }

    public function test_available_balance_excludes_pending_withdrawals(): void
    {
        $user = $this->student();
        $wallet = $this->svc->walletFor($user->id);
        $this->svc->credit($wallet, 100000, 'deposit', 'seed');

        $this->svc->requestWithdrawal($user, 40000, '+256700000000', null, null);

        // Balance is still 100k (debit happens on approval), but only 60k is available.
        $this->assertSame(100000, $wallet->fresh()->balance);
        $this->assertSame(60000, $this->svc->availableBalance($wallet->fresh()));

        // A second request can't claim more than the remaining available.
        $this->expectException(\RuntimeException::class);
        $this->svc->requestWithdrawal($user, 70000, '+256700000000', null, null);
    }

    public function test_approving_a_withdrawal_debits_the_wallet(): void
    {
        $user = $this->student();
        $admin = User::factory()->create(['role' => 'admin']);
        $wallet = $this->svc->walletFor($user->id);
        $this->svc->credit($wallet, 100000, 'deposit', 'seed');

        $req = $this->svc->requestWithdrawal($user, 40000, '+256700000000', 'Jane', null);
        $this->svc->approveWithdrawal($req, $admin, 'PAYREF9');

        $wallet->refresh();
        $this->assertSame(60000, $wallet->balance);
        $this->assertSame(40000, $wallet->total_withdrawn);
        $this->assertSame('approved', $req->fresh()->status);
        $this->assertTrue($this->svc->verifyLedger($wallet));
    }

    // ── HTTP flow ────────────────────────────────────────────────────────────

    public function test_student_can_submit_a_deposit_request(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = $this->student();

        $this->actingAs($user)
            ->post(route('trade.live.deposit.store'), [
                'amount' => 50,
                'reference' => '0xabc123',
                'proof' => \Illuminate\Http\UploadedFile::fake()->image('payment.png'),
            ])
            ->assertRedirect(route('trade.live'));

        $this->assertDatabaseHas('deposit_requests', [
            'user_id' => $user->id, 'amount' => 50, 'reference' => '0xabc123', 'status' => 'pending',
        ]);
        // Proof screenshot was stored.
        $req = \App\Models\Trading\DepositRequest::where('user_id', $user->id)->first();
        $this->assertNotNull($req->proof_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($req->proof_path);
    }

    public function test_deposit_requires_a_proof_screenshot(): void
    {
        $user = $this->student();

        $this->actingAs($user)
            ->post(route('trade.live.deposit.store'), ['amount' => 50])
            ->assertSessionHasErrors('proof');

        $this->assertDatabaseCount('deposit_requests', 0);
    }

    public function test_admin_can_approve_a_deposit_via_http(): void
    {
        $user = $this->student();
        $admin = User::factory()->create(['role' => 'admin']);
        $req = DepositRequest::create([
            'user_id' => $user->id, 'amount' => 75000, 'reference' => 'R1', 'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.trading.live.deposits.approve', $req))
            ->assertRedirect();

        $this->assertSame('approved', $req->fresh()->status);
        $this->assertSame(75000, $this->svc->walletFor($user->id)->balance);
    }

    public function test_student_live_pages_render(): void
    {
        $user = $this->student();
        $this->svc->credit($this->svc->walletFor($user->id), 100000, 'deposit', 'seed');

        foreach (['trade.live', 'trade.live.deposit', 'trade.live.withdraw', 'trade.live.transactions'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_admin_live_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $wallet = $this->svc->walletFor($this->student()->id);

        $this->actingAs($admin);
        foreach (['overview', 'deposits', 'withdrawals', 'accounts', 'settings'] as $page) {
            $this->get(route("admin.trading.live.{$page}"))->assertOk();
        }
        $this->get(route('admin.trading.live.accounts.show', $wallet))->assertOk();
    }
}
