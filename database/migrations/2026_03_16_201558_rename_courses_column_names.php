<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'name') && ! Schema::hasColumn('courses', 'title')) {
                $table->renameColumn('name', 'title');
            }
            if (Schema::hasColumn('courses', 'image_path') && ! Schema::hasColumn('courses', 'image')) {
                $table->renameColumn('image_path', 'image');
            }
            if (Schema::hasColumn('courses', 'weekly_outline') && ! Schema::hasColumn('courses', 'outline')) {
                $table->renameColumn('weekly_outline', 'outline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'title') && ! Schema::hasColumn('courses', 'name')) {
                $table->renameColumn('title', 'name');
            }
            if (Schema::hasColumn('courses', 'image') && ! Schema::hasColumn('courses', 'image_path')) {
                $table->renameColumn('image', 'image_path');
            }
            if (Schema::hasColumn('courses', 'outline') && ! Schema::hasColumn('courses', 'weekly_outline')) {
                $table->renameColumn('outline', 'weekly_outline');
            }
        });
    }
};
