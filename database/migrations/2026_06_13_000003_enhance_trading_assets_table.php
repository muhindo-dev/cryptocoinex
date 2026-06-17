<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trading_assets', function (Blueprint $table) {
            if (! Schema::hasColumn('trading_assets', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('trading_assets', 'icon_url')) {
                $table->string('icon_url', 500)->nullable()->after('description');
            }
            if (! Schema::hasColumn('trading_assets', 'display_order')) {
                $table->smallInteger('display_order')->default(0)->after('icon_url');
            }
            if (! Schema::hasColumn('trading_assets', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('display_order');
            }
            if (! Schema::hasColumn('trading_assets', 'category')) {
                $table->string('category', 50)->default('crypto')->after('is_featured');
            }
            if (! Schema::hasColumn('trading_assets', 'difficulty')) {
                $table->string('difficulty', 20)->default('beginner')->after('category');
            }
            if (! Schema::hasColumn('trading_assets', 'tags')) {
                $table->json('tags')->nullable()->after('difficulty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trading_assets', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'icon_url', 'display_order',
                'is_featured', 'category', 'difficulty', 'tags',
            ]);
        });
    }
};
