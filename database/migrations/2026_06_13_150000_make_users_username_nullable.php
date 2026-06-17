<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The live users.username column drifted to NOT NULL (the table predates the
 * nullable migration), which broke self-registration on MySQL since the
 * onboarding flow doesn't require a username. Make it explicitly nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally a no-op — we never want username to be NOT NULL again.
    }
};
