<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('sender_id'); // user or admin
            $table->text('message');
            $table->boolean('is_admin')->default(false); // true if admin sent
            $table->timestamps();

            $table->index('chat_id');
            $table->index('sender_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
