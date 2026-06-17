<?php

namespace Tests\Feature\Trading;

use App\Models\Trading\TradingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Seed minimal settings so the page renders
        TradingSetting::set('default_start_balance', '10000');
        TradingSetting::set('default_mode', 'sim');
        TradingSetting::set('live_mode_enabled', 'false');
        TradingSetting::set('tie_policy', 'refund');
        TradingSetting::set('allow_student_reset', 'false');
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.trading.settings.index'))
            ->assertOk()
            ->assertSee('Trading Settings');
    }

    public function test_update_persists_settings(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.trading.settings.update'), [
                'default_start_balance' => 5000,
                'default_mode' => 'live',
                'live_mode_enabled' => '1',
                'tie_policy' => 'loss',
                'allow_student_reset' => '1',
            ])
            ->assertRedirect();

        $this->assertEquals('5000', TradingSetting::get('default_start_balance'));
        $this->assertEquals('live', TradingSetting::get('default_mode'));
        $this->assertEquals('true', TradingSetting::get('live_mode_enabled'));
        $this->assertEquals('loss', TradingSetting::get('tie_policy'));
        $this->assertEquals('true', TradingSetting::get('allow_student_reset'));
    }

    public function test_update_rejects_invalid_mode(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.trading.settings.update'), [
                'default_start_balance' => 10000,
                'default_mode' => 'invalid_mode',
                'tie_policy' => 'refund',
            ])
            ->assertSessionHasErrors('default_mode');
    }

    public function test_update_rejects_zero_balance(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.trading.settings.update'), [
                'default_start_balance' => 0,
                'default_mode' => 'sim',
                'tie_policy' => 'refund',
            ])
            ->assertSessionHasErrors('default_start_balance');
    }
}
