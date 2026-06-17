<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profit distributions — the way real returns are paid out.
 *
 * Instead of an automatic nightly percentage, an admin initiates a distribution
 * of a fixed pool amount. That pool is split across every member who holds a
 * live balance, in proportion to each member's share of the total live balance,
 * and credited to their Live Account as a positive transaction.
 *
 * Each distribution keeps an immutable per-member breakdown (shares) so admins
 * can follow up on exactly what every member received.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('total_amount');   // pool distributed
            $table->unsignedBigInteger('total_base');      // sum of member balances used as the basis
            $table->unsignedInteger('members_count');      // how many members shared in it
            $table->string('currency', 8)->default('USD');
            $table->text('note')->nullable();              // admin explanation, shown to members
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('live_distribution_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_distribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('live_wallet_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('base_balance');    // member's live balance at distribution time
            $table->decimal('percentage', 8, 4);           // member's % of the total base
            $table->unsignedBigInteger('amount');          // amount credited to this member
            $table->foreignId('live_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['live_distribution_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_distribution_shares');
        Schema::dropIfExists('live_distributions');
    }
};
