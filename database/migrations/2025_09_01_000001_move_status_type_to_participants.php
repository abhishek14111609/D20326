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
        // Add status and type to competition_participants
        Schema::table('competition_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('competition_participants', 'status')) {
                $table->enum('status', ['pending', 'registered', 'submitted', 'disqualified', 'winner'])->default('pending')->after('user_id');
            }
            
            if (!Schema::hasColumn('competition_participants', 'type')) {
                $table->enum('type', ['solo', 'team'])->default('solo')->after('status');
            }
        });
        
        // Remove status and type from competitions
        Schema::table('competitions', function (Blueprint $table) {
            if (Schema::hasColumn('competitions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('competitions', 'type')) {
                $table->dropColumn('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add status and type back to competitions
        Schema::table('competitions', function (Blueprint $table) {
            if (!Schema::hasColumn('competitions', 'status')) {
                $table->enum('status', ['draft', 'upcoming', 'active', 'completed', 'cancelled'])->default('draft');
            }
            if (!Schema::hasColumn('competitions', 'type')) {
                $table->enum('type', ['solo', 'team'])->default('solo');
            }
        });
        
        // Remove status and type from competition_participants
        Schema::table('competition_participants', function (Blueprint $table) {
            if (Schema::hasColumn('competition_participants', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('competition_participants', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
