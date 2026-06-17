<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        TradingSetting::set('default_start_balance', '10000');
    }

    public function test_index_lists_students(): void
    {
        $student = User::factory()->create(['name' => 'Alice Trader']);

        $this->actingAs($this->admin)
            ->get(route('admin.trading.students.index'))
            ->assertOk()
            ->assertSee('Alice Trader');
    }

    public function test_show_displays_student_wallet(): void
    {
        $student = User::factory()->create();
        app(WalletService::class)->grantStartingBalance($student->id);

        $this->actingAs($this->admin)
            ->get(route('admin.trading.students.show', $student))
            ->assertOk()
            ->assertSee('10,000');
    }

    public function test_topup_credits_wallet(): void
    {
        $student = User::factory()->create();
        $wallet = app(WalletService::class)->walletFor($student->id);

        $this->actingAs($this->admin)
            ->post(route('admin.trading.students.topup', $student), [
                'amount' => 500,
                'reason' => 'bonus',
            ])
            ->assertRedirect();

        $this->assertEquals(500, $wallet->fresh()->balance);
    }

    public function test_topup_rejects_zero_amount(): void
    {
        $student = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.trading.students.topup', $student), ['amount' => 0])
            ->assertSessionHasErrors('amount');
    }

    public function test_reset_restores_default_balance(): void
    {
        $student = User::factory()->create();
        $walletSvc = app(WalletService::class);
        $wallet = $walletSvc->walletFor($student->id);
        $walletSvc->credit($wallet, 5000, 'topup');

        $this->actingAs($this->admin)
            ->post(route('admin.trading.students.reset', $student))
            ->assertRedirect();

        $this->assertEquals(10000, $wallet->fresh()->balance);
    }

    public function test_search_filters_students(): void
    {
        User::factory()->create(['name' => 'Zara Unique', 'email' => 'zara@test.com']);
        User::factory()->create(['name' => 'Bob Other']);

        $this->actingAs($this->admin)
            ->get(route('admin.trading.students.index', ['search' => 'Zara']))
            ->assertOk()
            ->assertSee('Zara Unique')
            ->assertDontSee('Bob Other');
    }
}
