<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();

            // Optional: link to users table if instructors are also users
            $table->unsignedBigInteger('user_id')->nullable();

            // Core instructor info
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('expertise')->nullable();
            $table->integer('experience_years')->nullable();
            $table->text('bio')->nullable();
            $table->string('portfolio')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, etc.

            $table->timestamps();

            // Foreign key if instructors are also users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('instructors');
    }
};
