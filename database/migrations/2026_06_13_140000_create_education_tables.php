<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->string('icon', 60)->default('fa-book');     // FontAwesome
            $table->string('accent', 12)->default('#3b82f6');   // hex
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('education_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('education_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('level', ['beginner', 'base', 'advanced'])->default('beginner');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();               // structured sections
            $table->string('youtube_id', 20)->nullable();
            $table->string('video_title')->nullable();
            $table->string('duration', 12)->nullable();         // e.g. 01:19
            $table->string('thumbnail', 500)->nullable();
            $table->unsignedSmallInteger('read_minutes')->default(4);
            $table->boolean('is_recommended')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'sort_order']);
            $table->index('level');
        });

        Schema::create('education_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('education_articles')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->unique(['user_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_progress');
        Schema::dropIfExists('education_articles');
        Schema::dropIfExists('education_categories');
    }
};
