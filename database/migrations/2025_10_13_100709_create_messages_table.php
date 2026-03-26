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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Unique message identifier
            $table->unsignedBigInteger('conversation_id'); // Which conversation this message belongs to
            $table->unsignedBigInteger('sender_id'); // Who sent the message
            $table->unsignedBigInteger('receiver_id'); // Who received the message
            $table->text('message'); // Message content
            $table->enum('type', ['text', 'media', 'system', 'voice', 'location'])->default('text'); // Message type
            $table->string('media_path')->nullable(); // Path to media file if applicable
            $table->enum('media_type', ['image', 'video', 'audio', 'document'])->nullable(); // Type of media
            $table->timestamp('read_at')->nullable(); // When the message was read
            $table->timestamp('delivered_at')->nullable(); // When the message was delivered
            $table->timestamps();
            $table->softDeletes(); // Soft delete support

            // Indexes for better performance
            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'read_at']);
            $table->index('uuid');

            // Foreign key constraints
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};