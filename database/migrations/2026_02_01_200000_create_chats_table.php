<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // The user (not admin)
            $table->unsignedBigInteger('admin_id')->nullable(); // The admin assigned (nullable for unassigned)
            $table->string('subject')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->index('user_id');
            $table->index('admin_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
