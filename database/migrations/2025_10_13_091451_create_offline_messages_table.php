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
        Schema::create('offline_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('message_data');
            $table->timestamp('stored_at');
            $table->boolean('delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'delivered']);
            $table->index('stored_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_messages');
    }
};
