<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80);          // first_trade, win_streak_3, profit_master, ...
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('achieved_at');
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->unique(['user_id', 'type']);   // each badge awarded once per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_achievements');
    }
};
