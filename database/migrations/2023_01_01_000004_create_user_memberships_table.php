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
        Schema::create('user_memberships', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('membership_plan_id')->constrained()->onDelete('cascade');
            
            // Subscription details
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->dateTime('cancelled_at')->nullable();
            $table->enum('status', [
                'active',
                'cancelling',
                'cancelled',
                'expired',
                'paused',
                'payment_failed',
            ])->default('active');
            
            // Billing information
            $table->boolean('auto_renew')->default(true);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['ends_at', 'status']);
            $table->index(['auto_renew', 'status']);
        });
        
        // Add a membership_level column to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('membership_level', ['free', 'premium', 'vip', 'enterprise'])
                  ->default('free')
                  ->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_memberships');
        
        // Remove the membership_level column if it exists
        if (Schema::hasColumn('users', 'membership_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('membership_level');
            });
        }
    }
};
