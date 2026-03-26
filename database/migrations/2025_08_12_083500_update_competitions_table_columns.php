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
        Schema::table('competitions', function (Blueprint $table) {
            // Rename columns to match form field names
            $table->renameColumn('start_date', 'competition_start');
            $table->renameColumn('end_date', 'competition_end');
            
            // Add missing columns
            $table->string('timezone')->default('UTC')->after('competition_end');
            $table->integer('min_team_size')->nullable()->after('min_participants');
            $table->integer('max_team_size')->nullable()->after('min_team_size');
            $table->string('currency', 3)->default('USD')->after('entry_fee');
            $table->text('terms')->nullable()->after('rules');
            $table->integer('sort_order')->default(0)->after('is_featured');
            $table->json('tags')->nullable()->after('metadata');
            
            // Update enum types to match form options
            $table->enum('status', ['draft', 'upcoming', 'active', 'completed', 'cancelled'])->default('draft')->change();
            $table->enum('type', ['solo', 'team', 'tournament', 'league'])->default('solo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            // Revert column renames
            $table->renameColumn('competition_start', 'start_date');
            $table->renameColumn('competition_end', 'end_date');
            
            // Remove added columns
            $table->dropColumn([
                'timezone',
                'min_team_size',
                'max_team_size',
                'currency',
                'terms',
                'sort_order',
                'tags'
            ]);
            
            // Revert enum types (note: this is simplified - actual reversion may need more complex handling)
            $table->enum('status', ['upcoming', 'registration_open', 'in_progress', 'judging', 'completed', 'cancelled'])->default('upcoming')->change();
            $table->enum('type', ['photo', 'video', 'art', 'writing', 'other'])->change();
        });
    }
};
