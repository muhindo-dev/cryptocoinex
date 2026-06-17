<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 30)->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('country');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 60)->nullable()->default('Africa/Kampala')->after('city');
            }
            if (! Schema::hasColumn('users', 'trading_experience')) {
                $table->string('trading_experience', 30)->nullable()->default('beginner')->after('timezone');
            }
            if (! Schema::hasColumn('users', 'preferred_assets')) {
                $table->json('preferred_assets')->nullable()->after('trading_experience');
            }
            if (! Schema::hasColumn('users', 'notification_prefs')) {
                $table->json('notification_prefs')->nullable()->after('preferred_assets');
            }
            if (! Schema::hasColumn('users', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('notification_prefs');
            }
            if (! Schema::hasColumn('users', 'cover_photo')) {
                $table->string('cover_photo', 500)->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'twitter_handle')) {
                $table->string('twitter_handle', 100)->nullable()->after('cover_photo');
            }
            if (! Schema::hasColumn('users', 'instagram_handle')) {
                $table->string('instagram_handle', 100)->nullable()->after('twitter_handle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth', 'gender', 'country', 'city', 'timezone',
                'trading_experience', 'preferred_assets', 'notification_prefs',
                'last_active_at', 'cover_photo', 'twitter_handle', 'instagram_handle',
            ]);
        });
    }
};
