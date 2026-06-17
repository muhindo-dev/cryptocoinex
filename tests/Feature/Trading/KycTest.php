<?php

namespace Tests\Feature\Trading;

use App\Models\KycSubmission;
use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;
use App\Models\User;
use App\Services\Trading\KycService;
use App\Services\Trading\LiveWalletService;
use App\Services\Trading\TradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KycTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Storage::fake('local');
        TradingSetting::set('live_account_enabled', 'true');
        TradingSetting::set('live_account_currency', 'USD');

        $this->user = User::factory()->create(['role' => 'student', 'kyc_status' => 'unverified']);
        $this->asset = Asset::factory()->create([
            'symbol' => 'SIMBTC', 'asset_class' => 'sim', 'supports_live' => false, 'enabled' => true,
            'min_stake' => 1, 'max_stake' => 100000, 'payout_percent' => 80, 'allowed_expiries' => [30, 60, 300],
        ]);
    }

    private function fundLive(int $amount = 1000): void
    {
        $live = app(LiveWalletService::class);
        $live->credit($live->walletFor($this->user->id), $amount, 'deposit', 'seed');
    }

    // ── Gating ────────────────────────────────────────────────────────────────

    public function test_live_trade_is_blocked_until_kyc_approved(): void
    {
        $this->fundLive();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Verify your identity');
        app(TradeService::class)->open($this->user, $this->asset, 'sim', 'up', 100, null, 'live');
    }

    public function test_live_trade_works_once_kyc_approved(): void
    {
        $this->fundLive();
        $this->user->forceFill(['kyc_status' => 'approved'])->save();

        $trade = app(TradeService::class)->open($this->user->fresh(), $this->asset, 'sim', 'up', 100, null, 'live');
        $this->assertSame('live', $trade->account);
    }

    public function test_demo_trade_also_requires_kyc(): void
    {
        app(\App\Services\Trading\WalletService::class)->grantStartingBalance($this->user->id);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Verify your identity');
        app(TradeService::class)->open($this->user, $this->asset, 'sim', 'up', 100); // demo, unverified
    }

    public function test_trading_works_for_any_account_once_approved(): void
    {
        $this->user->forceFill(['kyc_status' => 'approved'])->save();
        app(\App\Services\Trading\WalletService::class)->grantStartingBalance($this->user->id);
        $trade = app(TradeService::class)->open($this->user->fresh(), $this->asset, 'sim', 'up', 100); // demo
        $this->assertSame('demo', $trade->account);
    }

    public function test_deposit_and_withdraw_redirect_to_kyc_when_unverified(): void
    {
        $this->actingAs($this->user)->get(route('trade.live.deposit'))->assertRedirect(route('trade.kyc'));
        $this->actingAs($this->user)->get(route('trade.live.withdraw'))->assertRedirect(route('trade.kyc'));
        $this->actingAs($this->user)->post(route('trade.live.deposit.store'), ['amount' => 50])
            ->assertRedirect(route('trade.kyc'));
    }

    // ── Submission ────────────────────────────────────────────────────────────

    public function test_submitting_kyc_creates_a_pending_record_and_stores_doc_privately(): void
    {
        $this->actingAs($this->user)->post(route('trade.kyc.store'), [
            'full_name' => 'Jane Q Public',
            'document_type' => 'passport',
            'document_number' => 'A1234567',
            'document' => UploadedFile::fake()->image('passport.jpg'),
            'message' => 'thanks',
        ])->assertRedirect(route('trade.kyc'));

        $sub = KycSubmission::where('user_id', $this->user->id)->first();
        $this->assertNotNull($sub);
        $this->assertSame('pending', $sub->status);
        $this->assertSame('pending', $this->user->fresh()->kyc_status);

        // Document stored on the PRIVATE disk, never the public one.
        Storage::disk('local')->assertExists($sub->document_path);
        $this->assertStringStartsWith('kyc_documents/', $sub->document_path);
    }

    public function test_cannot_submit_while_pending(): void
    {
        $this->user->forceFill(['kyc_status' => 'pending'])->save();
        $this->actingAs($this->user)->post(route('trade.kyc.store'), [
            'full_name' => 'X', 'document_type' => 'passport', 'document_number' => 'Y',
            'document' => UploadedFile::fake()->image('p.jpg'),
        ])->assertRedirect(route('trade.kyc'));

        $this->assertDatabaseCount('kyc_submissions', 0);
    }

    public function test_document_requires_an_upload(): void
    {
        $this->actingAs($this->user)->post(route('trade.kyc.store'), [
            'full_name' => 'X', 'document_type' => 'passport', 'document_number' => 'Y',
        ])->assertSessionHasErrors('document');
    }

    // ── Admin review ──────────────────────────────────────────────────────────

    private function pendingSubmission(): KycSubmission
    {
        return app(KycService::class)->submit($this->user, [
            'full_name' => 'Jane', 'document_type' => 'passport', 'document_number' => 'A1',
        ], 'kyc_documents/x.jpg');
    }

    public function test_admin_approve_verifies_the_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sub = $this->pendingSubmission();

        $this->actingAs($admin)->post(route('admin.trading.kyc.approve', $sub))->assertRedirect();

        $this->assertSame('approved', $sub->fresh()->status);
        $this->assertSame('approved', $this->user->fresh()->kyc_status);
        $this->assertNotNull($this->user->fresh()->kyc_verified_at);
    }

    public function test_admin_redo_lets_user_resubmit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sub = $this->pendingSubmission();

        $this->actingAs($admin)->post(route('admin.trading.kyc.redo', $sub), ['admin_note' => 'Photo too blurry'])->assertRedirect();

        $this->assertSame('resubmit', $this->user->fresh()->kyc_status);
        $this->assertTrue($this->user->fresh()->canSubmitKyc());
        $this->assertSame('Photo too blurry', $sub->fresh()->admin_note);
    }

    public function test_cannot_review_the_same_submission_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sub = $this->pendingSubmission();
        app(KycService::class)->approve($sub, $admin);

        $this->expectException(\RuntimeException::class);
        app(KycService::class)->decline($sub->fresh(), $admin, 'late');
    }

    public function test_document_route_is_admin_only(): void
    {
        $sub = $this->pendingSubmission();

        // A normal student is denied (the admin middleware bounces non-staff to login).
        $this->actingAs($this->user)->get(route('admin.trading.kyc.document', $sub))
            ->assertRedirect(route('admin.login'));

        // And a guest cannot reach it either.
        $this->get(route('admin.trading.kyc.document', $sub))->assertRedirect();
    }
}
