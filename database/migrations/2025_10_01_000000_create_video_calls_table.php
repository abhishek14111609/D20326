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
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique();
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('agora_channel');
            $table->string('agora_token')->nullable();
            $table->string('agora_rtm_token')->nullable();
            $table->string('status')->default('initiated');
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_enabled')->default(true);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_calls');
    }
};
