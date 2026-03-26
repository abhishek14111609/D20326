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
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Point information
            $table->integer('points')->default(0);
            $table->string('action_type', 50); // e.g., 'profile_view', 'match', 'message_sent', 'referral', etc.
            $table->string('source_type')->nullable(); // The type of the related model (e.g., 'App\Models\User', 'App\Models\Message')
            $table->unsignedBigInteger('source_id')->nullable(); // The ID of the related model
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'action_type']);
            $table->index(['source_type', 'source_id']);
            $table->index('created_at');
        });
        
        // Add a total_points column to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_points')->default(0)->after('membership_level');
            $table->unsignedInteger('weekly_points')->default(0)->after('total_points');
            $table->unsignedInteger('monthly_points')->default(0)->after('weekly_points');
            $table->unsignedInteger('daily_points')->default(0)->after('monthly_points');
            $table->timestamp('last_points_reset_at')->nullable()->after('daily_points');
            
            // Indexes
            $table->index('total_points');
            $table->index('weekly_points');
            $table->index('monthly_points');
            $table->index('daily_points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_points');
        
        // Remove the points columns from the users table if they exist
        if (Schema::hasColumn('users', 'total_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['total_points', 'weekly_points', 'monthly_points', 'daily_points', 'last_points_reset_at']);
            });
        }
    }
};
