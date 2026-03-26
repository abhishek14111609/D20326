<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the new columns without any after() clauses
        Schema::table('competitions', function (Blueprint $table) {
            $table->json('tags')->nullable();
            $table->string('timezone', 100)->default('UTC');
            
            // Add the new competition_type column
            $table->enum('competition_type', [
                'solo', 
                'team', 
                'tournament', 
                'league',
                'photo', 
                'video', 
                'art', 
                'writing', 
                'other'
            ])->nullable();
        });

        // Copy data from old type column to new competition_type column if it exists
        if (Schema::hasColumn('competitions', 'type')) {
            DB::statement('UPDATE competitions SET competition_type = `type`');
        }

        // Set default value for competition_type
        DB::statement("UPDATE competitions SET competition_type = 'solo' WHERE competition_type IS NULL");

        // Make competition_type not nullable
        Schema::table('competitions', function (Blueprint $table) {
            $table->enum('competition_type', [
                'solo', 
                'team', 
                'tournament', 
                'league',
                'photo', 
                'video', 
                'art', 
                'writing', 
                'other'
            ])->default('solo')->nullable(false)->change();

            // Update the status enum if it exists
            if (Schema::hasColumn('competitions', 'status')) {
                $table->enum('status', [
                    'draft',
                    'upcoming', 
                    'active', 
                    'registration_open', 
                    'in_progress', 
                    'judging', 
                    'completed', 
                    'cancelled'
                ])->default('draft')->change();
            }
        });

        // Drop the old type column if it exists
        Schema::table('competitions', function (Blueprint $table) {
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
        // Add back the old type column if it doesn't exist
        if (!Schema::hasColumn('competitions', 'type')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->enum('type', ['photo', 'video', 'art', 'writing', 'other'])->after('competition_type');
            });
        }

        // Copy data back if competition_type exists
        if (Schema::hasColumn('competitions', 'competition_type')) {
            DB::statement('UPDATE competitions SET `type` = competition_type');
        }

        // Drop the new columns
        Schema::table('competitions', function (Blueprint $table) {
            $columnsToDrop = collect(['competition_type', 'tags', 'timezone'])
                ->filter(fn($column) => Schema::hasColumn('competitions', $column))
                ->toArray();
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            // Revert status enum if it exists
            if (Schema::hasColumn('competitions', 'status')) {
                $table->enum('status', [
                    'upcoming', 
                    'registration_open', 
                    'in_progress', 
                    'judging', 
                    'completed', 
                    'cancelled'
                ])->default('upcoming')->change();
            }
        });
    }
};