<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trading_trades', function (Blueprint $table) {
            if (! Schema::hasColumn('trading_trades', 'notes')) {
                $table->text('notes')->nullable()->after('payout_amount');
            }
            if (! Schema::hasColumn('trading_trades', 'tags')) {
                $table->json('tags')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('trading_trades', 'sentiment')) {
                $table->string('sentiment', 20)->nullable()->after('tags'); // confident|unsure|fomo
            }
            if (! Schema::hasColumn('trading_trades', 'device_type')) {
                $table->string('device_type', 30)->nullable()->after('sentiment'); // desktop|mobile
            }
        });
    }

    public function down(): void
    {
        Schema::table('trading_trades', function (Blueprint $table) {
            $table->dropColumn(['notes', 'tags', 'sentiment', 'device_type']);
        });
    }
};
