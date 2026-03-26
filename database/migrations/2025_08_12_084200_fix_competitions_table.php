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
        // Rename columns
        if (Schema::hasColumn('competitions', 'start_date')) {
            DB::statement('ALTER TABLE competitions CHANGE start_date competition_start DATETIME');
        }
        
        if (Schema::hasColumn('competitions', 'end_date')) {
            DB::statement('ALTER TABLE competitions CHANGE end_date competition_end DATETIME');
        }
        
        // Add missing columns if they don't exist
        if (!Schema::hasColumn('competitions', 'timezone')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->string('timezone')->default('UTC')->after('competition_end');
            });
        }
        
        if (!Schema::hasColumn('competitions', 'min_team_size')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->integer('min_team_size')->nullable()->after('min_participants');
            });
        }
        
        if (!Schema::hasColumn('competitions', 'max_team_size')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->integer('max_team_size')->nullable()->after('min_team_size');
            });
        }
        
        if (!Schema::hasColumn('competitions', 'currency')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->string('currency', 3)->default('USD')->after('entry_fee');
            });
        }
        
        if (!Schema::hasColumn('competitions', 'terms')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->text('terms')->nullable()->after('rules');
            });
        }
        
        if (!Schema::hasColumn('competitions', 'sort_order')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('is_featured');
            });
        }
        
        if (!Schema::hasColumn('competitions', 'tags')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->json('tags')->nullable()->after('metadata');
            });
        }
        
        // Update enum types if needed (this requires raw SQL for some database systems)
        DB::statement("ALTER TABLE competitions MODIFY status ENUM('draft', 'upcoming', 'active', 'completed', 'cancelled') DEFAULT 'draft'");
        DB::statement("ALTER TABLE competitions MODIFY type ENUM('solo', 'team', 'tournament', 'league') DEFAULT 'solo'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration - we won't implement down() to avoid data loss
    }
};
