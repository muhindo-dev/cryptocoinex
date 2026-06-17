<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('asset_id')->nullable()->constrained('trading_assets')->nullOnDelete(); // null = any asset
            $table->bigInteger('starting_balance')->default(5000);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', ['upcoming', 'active', 'ended'])->default('upcoming');
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });

        Schema::create('trading_tournament_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('trading_tournaments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at');
            $table->bigInteger('final_balance')->nullable();
            $table->bigInteger('final_pnl')->nullable();
            $table->integer('final_rank')->nullable();

            $table->unique(['tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_tournament_participants');
        Schema::dropIfExists('trading_tournaments');
    }
};
