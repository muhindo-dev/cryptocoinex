<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'level')) {
                $table->string('level')->nullable()->after('description');
            }
            if (! Schema::hasColumn('courses', 'duration_weeks')) {
                $table->integer('duration_weeks')->nullable()->after('level');
            }
            if (! Schema::hasColumn('courses', 'schedule')) {
                $table->string('schedule')->nullable()->after('duration_weeks');
            }
            if (! Schema::hasColumn('courses', 'mode')) {
                $table->string('mode')->nullable()->after('schedule');
            }
            if (! Schema::hasColumn('courses', 'short_description')) {
                $table->string('short_description', 500)->nullable()->after('mode');
            }
            if (! Schema::hasColumn('courses', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('fee');
            }
            if (! Schema::hasColumn('courses', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['level', 'duration_weeks', 'schedule', 'mode', 'short_description', 'is_featured', 'is_active']);
        });
    }
};
