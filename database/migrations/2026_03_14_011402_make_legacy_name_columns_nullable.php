<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('students', 'first_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('first_name', 191)->nullable()->change();
            });
        }
        if (Schema::hasColumn('students', 'last_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('last_name', 191)->nullable()->change();
            });
        }
        if (Schema::hasColumn('instructors', 'first_name')) {
            Schema::table('instructors', function (Blueprint $table) {
                $table->string('first_name', 191)->nullable()->change();
            });
        }
        if (Schema::hasColumn('instructors', 'last_name')) {
            Schema::table('instructors', function (Blueprint $table) {
                $table->string('last_name', 191)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // no-op — reversing nullable changes on potentially absent columns is unsafe
    }
};
