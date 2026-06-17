<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\LiveDistribution;
use App\Models\Trading\LiveTransaction;
use App\Models\Trading\LiveWallet;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\LiveDistributionService;
use App\Services\Trading\LiveWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Profit distributions split a pool across live-balance holders proportionally.
 * Real money — the per-member amounts must sum to EXACTLY the pool, every time.
 */
class LiveDistributionTest extends TestCase
{
    use RefreshDatabase;

    private LiveDistributionService $svc;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        TradingSetting::set('live_account_enabled', 'true');
        TradingSetting::set('live_account_currency', 'USD');
        $this->svc = app(LiveDistributionService::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function member(int $balance): LiveWallet
    {
        $u = User::factory()->create(['role' => 'student']);
        $wallet = LiveWallet::create(['user_id' => $u->id, 'currency' => 'USD', 'balance' => 0]);
        if ($balance > 0) {
            // Fund via a real deposit so the ledger (sum of transactions) matches.
            app(LiveWalletService::class)->credit($wallet, $balance, 'deposit', 'seed');
        }

        return $wallet->fresh();
    }

    public function test_pool_is_split_proportionally_and_sums_exactly(): void
    {
        $a = $this->member(500);
        $b = $this->member(300);
        $c = $this->member(200);

        $dist = $this->svc->distribute(100, $this->admin, 'Q1');

        $this->assertSame(100, $dist->total_amount);
        $this->assertSame(1000, $dist->total_base);
        $this->assertSame(3, $dist->members_count);
        $this->assertSame(100, (int) $dist->shares()->sum('amount')); // EXACT

        $this->assertSame(550, $a->fresh()->balance); // 500 + 50
        $this->assertSame(330, $b->fresh()->balance); // 300 + 30
        $this->assertSame(220, $c->fresh()->balance); // 200 + 20
    }

    public function test_leftover_units_are_apportioned_with_zero_drift(): void
    {
        // 3 members, pool that doesn't divide evenly: floors lose 2 units total.
        $this->member(100);
        $this->member(100);
        $this->member(100);

        $dist = $this->svc->distribute(100, $this->admin);
        // 100/3 each = 33.33 → floors 33,33,33 = 99, leftover 1 goes to one member.
        $this->assertSame(100, (int) $dist->shares()->sum('amount'));
        $amounts = $dist->shares()->pluck('amount')->sort()->values()->all();
        $this->assertSame([33, 33, 34], $amounts);
    }

    public function test_each_member_gets_a_credit_transaction_and_record(): void
    {
        $a = $this->member(800);
        $b = $this->member(200);

        $dist = $this->svc->distribute(1000, $this->admin, 'results');

        $shareA = $dist->shares()->where('user_id', $a->user_id)->first();
        $this->assertSame(800, $shareA->amount);            // 80% of 1000
        $this->assertEquals('80.0000', $shareA->percentage);
        $this->assertNotNull($shareA->live_transaction_id);

        $tx = LiveTransaction::find($shareA->live_transaction_id);
        $this->assertSame('distribution', $tx->type);
        $this->assertSame(800, $tx->amount);
        $this->assertStringContainsString('distribution', strtolower($tx->description));

        // Distribution profit counts toward the wallet's lifetime profit.
        $this->assertSame(800, $a->fresh()->total_profit);
        $this->assertSame(200, $b->fresh()->total_profit);
    }

    public function test_members_without_a_balance_are_excluded(): void
    {
        $funded = $this->member(1000);
        $empty = $this->member(0);

        $dist = $this->svc->distribute(500, $this->admin);

        $this->assertSame(1, $dist->members_count);
        $this->assertSame(1500, $funded->fresh()->balance);
        $this->assertSame(0, $empty->fresh()->balance);
        $this->assertSame(0, $dist->shares()->where('user_id', $empty->user_id)->count());
    }

    public function test_distribute_throws_when_no_eligible_members(): void
    {
        $this->member(0);
        $this->expectException(\RuntimeException::class);
        $this->svc->distribute(100, $this->admin);
    }

    public function test_distribute_throws_on_zero_amount(): void
    {
        $this->member(1000);
        $this->expectException(\RuntimeException::class);
        $this->svc->distribute(0, $this->admin);
    }

    public function test_all_wallet_ledgers_stay_consistent_after_distribution(): void
    {
        $this->member(700);
        $this->member(300);
        $this->svc->distribute(123, $this->admin);

        $live = app(LiveWalletService::class);
        foreach (LiveWallet::all() as $w) {
            $this->assertTrue($live->verifyLedger($w), "ledger drift on wallet {$w->id}");
        }
    }

    public function test_admin_can_create_a_distribution_via_http(): void
    {
        $this->member(600);
        $this->member(400);

        $res = $this->actingAs($this->admin)->post(route('admin.trading.live.distributions.store'), [
            'total_amount' => 250,
            'note' => 'October profits',
            'confirm' => '1',
        ]);

        $dist = LiveDistribution::latest()->first();
        $res->assertRedirect(route('admin.trading.live.distributions.show', $dist));
        $this->assertSame(250, (int) $dist->shares()->sum('amount'));
        $this->assertSame('October profits', $dist->note);
    }

    public function test_distribution_requires_confirmation(): void
    {
        $this->member(1000);
        $this->actingAs($this->admin)->post(route('admin.trading.live.distributions.store'), [
            'total_amount' => 100,
        ])->assertSessionHasErrors('confirm');

        $this->assertDatabaseCount('live_distributions', 0);
    }
}
