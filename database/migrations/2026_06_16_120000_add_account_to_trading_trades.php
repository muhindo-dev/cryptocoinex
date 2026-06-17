<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which wallet a trade is settled against:
 *   demo  → the virtual practice wallet (trading_wallets)
 *   live  → the user's real-money Live Account (live_wallets)
 *
 * Existing trades are all practice trades, so default 'demo'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trading_trades', function (Blueprint $table) {
            $table->string('account', 8)->default('demo')->after('mode')->index();
        });
    }

    public function down(): void
    {
        Schema::table('trading_trades', function (Blueprint $table) {
            $table->dropIndex(['account']);
            $table->dropColumn('account');
        });
    }
};
