<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('name');
            $table->enum('asset_class', ['crypto', 'forex', 'stock', 'sim']);
            $table->decimal('payout_percent', 5, 2)->default(80.00);
            $table->unsignedInteger('min_stake')->default(1);
            $table->unsignedInteger('max_stake')->default(10000);
            $table->json('allowed_expiries')->nullable();
            $table->boolean('supports_live')->default(false);
            $table->string('live_symbol')->nullable();
            $table->decimal('sim_start_price', 18, 8)->default(100.00000000);
            $table->decimal('sim_drift', 8, 5)->default(0.00000);
            $table->decimal('sim_volatility', 8, 5)->default(0.01000);
            $table->unsignedBigInteger('sim_seed')->default(42);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_assets');
    }
};
