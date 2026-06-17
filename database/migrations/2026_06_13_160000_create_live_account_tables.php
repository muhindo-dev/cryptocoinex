<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Live Account — a real-money wallet kept strictly separate from the
 * practice/simulated trading wallet. Money is deposited via mobile money,
 * verified and credited by an admin, earns a daily managed-trading return on
 * the matured balance, and is withdrawn back to mobile money on admin approval.
 *
 * All amounts are stored as whole-shilling integers (UGX has no practical minor
 * unit), so the ledger is exact — no floating-point money anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── The wallet: one per user, with cached running totals ──────────────
        Schema::create('live_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency', 8)->default('USD');
            $table->unsignedBigInteger('balance')->default(0);          // current balance
            $table->unsignedBigInteger('total_deposited')->default(0);  // lifetime in
            $table->unsignedBigInteger('total_withdrawn')->default(0);  // lifetime out
            $table->unsignedBigInteger('total_profit')->default(0);     // lifetime returns
            $table->timestamp('last_accrued_on')->nullable();           // last profit date
            $table->timestamps();
        });

        // ── Append-only ledger. Every balance change is one immutable row. ────
        Schema::create('live_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_wallet_id')->constrained()->cascadeOnDelete();
            // deposit | withdrawal | profit | adjustment
            $table->string('type', 24);
            $table->bigInteger('amount');               // signed: +credit / -debit
            $table->unsignedBigInteger('balance_after'); // balance immediately after
            $table->string('description', 255)->nullable();
            // Links back to the originating request (if any) for a full audit trail.
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            // Profit idempotency: one profit row per wallet per calendar day.
            // NULL for non-profit rows (MySQL treats NULLs as distinct, so the
            // unique index never blocks deposits/withdrawals/adjustments).
            $table->date('accrual_date')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['live_wallet_id', 'created_at']);
            $table->index(['source_type', 'source_id']);
            $table->unique(['live_wallet_id', 'accrual_date'], 'live_tx_daily_profit_unique');
        });

        // ── Deposit requests: user declares a mobile-money payment for review ─
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('reference', 120);               // mobile-money / txn ref
            $table->string('payer_phone', 32)->nullable();  // number they paid from
            $table->string('paid_to', 32)->nullable();      // which of our numbers
            $table->text('note')->nullable();               // user message
            // pending | approved | declined
            $table->string('status', 16)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('live_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });

        // ── Withdrawal requests: user asks to cash out to a mobile number ─────
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('payout_phone', 32);             // where to send money
            $table->string('payout_name', 120)->nullable(); // account holder name
            $table->text('note')->nullable();
            // pending | approved | declined
            $table->string('status', 16)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->string('payout_reference', 120)->nullable(); // admin's txn ref
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('live_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('deposit_requests');
        Schema::dropIfExists('live_transactions');
        Schema::dropIfExists('live_wallets');
    }
};
