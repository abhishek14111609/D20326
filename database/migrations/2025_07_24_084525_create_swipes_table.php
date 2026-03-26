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
        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swiper_id');
            $table->unsignedBigInteger('swiped_id');
            $table->enum('type', ['like', 'dislike', 'superlike']);
            $table->boolean('matched')->default(false);
            $table->timestamps();
            
            $table->foreign('swiper_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('swiped_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swipes');
    }
};
