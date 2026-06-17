<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Move the Live Account to USD crypto funding: deposits are paid in crypto
 * (USDT/USD) to a configured address and backed by an uploaded proof-of-payment
 * screenshot; withdrawals are paid out to a crypto wallet address.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->string('proof_path', 255)->nullable()->after('note'); // screenshot
            $table->string('reference', 191)->nullable()->change();        // txn hash now optional
            $table->string('paid_to', 191)->nullable()->change();          // crypto address snapshot
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('payout_address', 191)->nullable()->after('amount'); // crypto address
            $table->string('payout_network', 40)->nullable()->after('payout_address'); // e.g. USDT TRC20
            $table->string('payout_phone', 191)->nullable()->change();     // legacy, kept for old rows
        });
    }

    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn(['payout_address', 'payout_network']);
        });
    }
};
