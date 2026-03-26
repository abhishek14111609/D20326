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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('mobile')->unique()->nullable();
            $table->text('bio')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->json('location')->nullable(); // {"latitude": 0.0, "longitude": 0.0, "address": ""}
            $table->json('interest')->nullable(); // ["music", "sports", "travel"]
            $table->json('hobby')->nullable(); // ["reading", "cooking", "gaming"]
            $table->json('gallery_images')->nullable(); // ["path1.jpg", "path2.jpg"]
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
