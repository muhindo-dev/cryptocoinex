<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trading_wallets', function (Blueprint $table) {
            if (! Schema::hasColumn('trading_wallets', 'peak_balance')) {
                $table->bigInteger('peak_balance')->default(0)->after('balance');
            }
            if (! Schema::hasColumn('trading_wallets', 'total_credited')) {
                $table->bigInteger('total_credited')->default(0)->after('peak_balance');
            }
            if (! Schema::hasColumn('trading_wallets', 'total_debited')) {
                $table->bigInteger('total_debited')->default(0)->after('total_credited');
            }
            if (! Schema::hasColumn('trading_wallets', 'resets_count')) {
                $table->integer('resets_count')->default(0)->after('total_debited');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trading_wallets', function (Blueprint $table) {
            $table->dropColumn(['peak_balance', 'total_credited', 'total_debited', 'resets_count']);
        });
    }
};
