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
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'dob')) {
                $table->date('dob')->nullable();
            }
            if (! Schema::hasColumn('students', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (! Schema::hasColumn('students', 'username')) {
                $table->string('username')->nullable();
            }
            if (! Schema::hasColumn('students', 'location')) {
                $table->string('location')->nullable();
            }
            if (! Schema::hasColumn('students', 'goals')) {
                $table->text('goals')->nullable();
            }
            if (! Schema::hasColumn('students', 'status')) {
                $table->string('status')->default('pending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['username', 'dob', 'location', 'goals', 'status']);
        });
    }
};
