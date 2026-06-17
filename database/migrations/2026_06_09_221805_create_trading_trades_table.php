<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('trading_assets');
            $table->enum('mode', ['sim', 'live']);
            $table->enum('direction', ['up', 'down']);
            $table->bigInteger('stake');
            $table->decimal('payout_percent', 5, 2);
            $table->decimal('entry_price', 18, 8);
            $table->decimal('exit_price', 18, 8)->nullable();
            $table->dateTime('opened_at');
            $table->dateTime('expires_at');
            $table->dateTime('settled_at')->nullable();
            $table->unsignedInteger('expiry_seconds');
            $table->enum('status', ['open', 'won', 'lost', 'tie', 'void'])->default('open');
            $table->bigInteger('payout_amount')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_trades');
    }
};
