<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('trading_wallets')->cascadeOnDelete();
            $table->unsignedBigInteger('trade_id')->nullable();
            $table->enum('type', ['stake_hold', 'payout', 'refund', 'topup', 'reset', 'adjustment']);
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_wallet_entries');
    }
};
