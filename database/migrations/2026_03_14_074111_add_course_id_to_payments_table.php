<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after('student_id');
                $table->index('course_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'course_id')) {
                $table->dropIndex(['course_id']);
                $table->dropColumn('course_id');
            }
        });
    }
};
