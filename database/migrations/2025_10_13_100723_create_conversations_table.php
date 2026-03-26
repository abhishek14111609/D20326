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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user1_id'); // First user in conversation
            $table->unsignedBigInteger('user2_id'); // Second user in conversation
            $table->unsignedBigInteger('last_message_id')->nullable(); // Latest message ID
            $table->timestamp('last_message_at')->nullable(); // When the last message was sent
            $table->integer('unread_count_user1')->default(0); // Unread count for user1
            $table->integer('unread_count_user2')->default(0); // Unread count for user2
            $table->boolean('is_archived_user1')->default(false); // Is archived by user1
            $table->boolean('is_archived_user2')->default(false); // Is archived by user2
            $table->timestamps();

            // Ensure unique conversation between two users
            $table->unique(['user1_id', 'user2_id']);

            // Indexes for better performance
            $table->index(['user1_id', 'last_message_at']);
            $table->index(['user2_id', 'last_message_at']);
            $table->index('last_message_at');

            // Foreign key constraints
            $table->foreign('user1_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user2_id')->references('id')->on('users')->onDelete('cascade');
            // Note: last_message_id foreign key will be added after messages table is created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};