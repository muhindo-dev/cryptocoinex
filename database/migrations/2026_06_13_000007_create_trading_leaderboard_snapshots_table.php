<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 20);        // daily, weekly, all_time
            $table->date('period_date');
            $table->integer('rank');
            $table->integer('trades_count')->default(0);
            $table->decimal('win_rate', 5, 2)->default(0);
            $table->bigInteger('net_pnl')->default(0);
            $table->bigInteger('peak_balance')->default(0);
            $table->bigInteger('score')->default(0);
            $table->timestamp('computed_at');

            $table->unique(['user_id', 'period', 'period_date']);
            $table->index(['period', 'period_date', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_leaderboard_snapshots');
    }
};
