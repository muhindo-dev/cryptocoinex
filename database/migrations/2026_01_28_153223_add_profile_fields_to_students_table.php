<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('username')->nullable()->after('full_name');
            $table->date('dob')->nullable()->after('username');
            $table->string('location')->nullable()->after('dob');
            $table->text('motivation')->nullable()->after('course_interest');
            $table->boolean('agreed_terms')->default(false)->after('motivation');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['username', 'dob', 'location', 'motivation', 'agreed_terms']);
        });
    }
};
