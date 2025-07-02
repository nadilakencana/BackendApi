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
        Schema::create('history_chats', function (Blueprint $table) {
            $table->id();
            $table->string('id_session',100);
            $table->foreign('id_session')->references('id')->on('session_chats')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('sender_type', ['user', 'ai']);
            $table->longText('message_chat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_chats');
    }
};
