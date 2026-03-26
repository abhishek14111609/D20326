<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->enum('call_type', ['audio', 'video']);
            $table->enum('status', ['initiated', 'ongoing', 'completed', 'missed', 'rejected', 'failed'])->default('initiated');
            $table->decimal('call_cost', 10, 2);
            $table->integer('duration_minutes');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('call_identifier')->unique()->comment('Unique identifier for the call session');
            $table->text('end_reason')->nullable();
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['caller_id', 'status']);
            $table->index(['receiver_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_calls');
    }
};
