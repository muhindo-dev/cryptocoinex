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
        Schema::table('instructors', function (Blueprint $table) {
            if (! Schema::hasColumn('instructors', 'full_name')) {
                $table->string('full_name')->nullable();
            }
            if (! Schema::hasColumn('instructors', 'expertise')) {
                $table->string('expertise')->nullable();
            }
            if (! Schema::hasColumn('instructors', 'experience_years')) {
                $table->integer('experience_years')->nullable();
            }
            if (! Schema::hasColumn('instructors', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (! Schema::hasColumn('instructors', 'portfolio')) {
                $table->string('portfolio')->nullable();
            }
            if (! Schema::hasColumn('instructors', 'status')) {
                $table->string('status')->default('pending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            //
        });
    }
};
