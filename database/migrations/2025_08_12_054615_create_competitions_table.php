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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->dateTime('registration_start');
            $table->dateTime('registration_end');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['upcoming', 'registration_open', 'in_progress', 'judging', 'completed', 'cancelled'])->default('upcoming');
            $table->enum('type', ['photo', 'video', 'art', 'writing', 'other']);
            $table->integer('max_participants')->nullable();
            $table->integer('min_participants')->default(1);
            $table->integer('entry_fee')->default(0);
            $table->text('prizes')->nullable();
            $table->text('rules')->nullable();
            $table->text('judging_criteria')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
