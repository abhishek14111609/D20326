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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('mobile');
            $table->string('otp_code', 4);
            $table->enum('type', ['register', 'login', 'forgot_password']);
            $table->enum('status', ['pending', 'verified', 'expired']);
            $table->timestamp('expires_at');
            $table->integer('attempts')->default(0);
            $table->string('ip_address')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['mobile', 'type']);
            $table->index(['otp_code', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
