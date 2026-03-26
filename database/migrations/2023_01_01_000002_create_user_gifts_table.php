<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('gift_id')->constrained('gifts')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_gifts');
    }
};
