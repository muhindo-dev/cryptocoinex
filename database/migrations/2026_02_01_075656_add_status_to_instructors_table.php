<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            if (! Schema::hasColumn('instructors', 'status')) {
                $table->string('status')->default('pending')->after('portfolio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            if (Schema::hasColumn('instructors', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
