<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Students: add columns used by the application form
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'full_name')) {
                $table->string('full_name')->nullable()->after('id');
            }
            if (! Schema::hasColumn('students', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('full_name');
            }
            if (! Schema::hasColumn('students', 'location')) {
                $table->string('location')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('students', 'course_interest')) {
                $table->string('course_interest')->nullable()->after('location');
            }
            if (! Schema::hasColumn('students', 'goals')) {
                $table->text('goals')->nullable()->after('course_interest');
            }
            if (! Schema::hasColumn('students', 'agreed_terms')) {
                $table->boolean('agreed_terms')->default(false)->after('goals');
            }
        });

        // Instructors: add columns used by the application form
        Schema::table('instructors', function (Blueprint $table) {
            if (! Schema::hasColumn('instructors', 'full_name')) {
                $table->string('full_name')->nullable()->after('id');
            }
            if (! Schema::hasColumn('instructors', 'expertise')) {
                $table->string('expertise')->nullable()->after('bio');
            }
            if (! Schema::hasColumn('instructors', 'experience_years')) {
                $table->unsignedSmallInteger('experience_years')->nullable()->after('expertise');
            }
            if (! Schema::hasColumn('instructors', 'portfolio')) {
                $table->string('portfolio', 500)->nullable()->after('experience_years');
            }
            if (! Schema::hasColumn('instructors', 'status')) {
                $table->string('status')->default('pending')->after('portfolio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = ['full_name', 'username', 'location', 'course_interest', 'goals', 'agreed_terms'];
            $drop = array_filter($cols, fn ($c) => Schema::hasColumn('students', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });

        Schema::table('instructors', function (Blueprint $table) {
            $cols = ['full_name', 'expertise', 'experience_years', 'portfolio', 'status'];
            $drop = array_filter($cols, fn ($c) => Schema::hasColumn('instructors', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
