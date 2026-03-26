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
        Schema::create('audio_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique();
            $table->foreignId('caller_id')->constrained('users');
            $table->foreignId('receiver_id')->constrained('users');
            $table->string('status');
            $table->string('agora_channel')->nullable();
            $table->string('agora_token')->nullable();
            $table->string('agora_rtm_token')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['caller_id', 'status']);
            $table->index(['receiver_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audio_calls');
    }
};
