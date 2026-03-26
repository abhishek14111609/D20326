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
        // Drop the existing tokens table if it exists
        Schema::dropIfExists('tokens');
        
        // Recreate the tokens table with proper structure
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 100);
            $table->string('device_type', 50)->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('login_method', 20)->default('mobile');
            $table->timestamp('otp_verified_at')->nullable();
            $table->text('user_details')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Add indexes
            $table->index(['user_id', 'token']);
            $table->index('expires_at');
            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the tokens table
        Schema::dropIfExists('tokens');
    }
};
