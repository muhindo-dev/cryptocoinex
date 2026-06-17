<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trading_wallet_entries', function (Blueprint $table) {
            $table->foreign('trade_id')
                ->references('id')
                ->on('trading_trades')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trading_wallet_entries', function (Blueprint $table) {
            $table->dropForeign(['trade_id']);
        });
    }
};
