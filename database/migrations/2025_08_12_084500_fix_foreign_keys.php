<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop all tables in the correct order to avoid foreign key constraints
        $tables = [
            'personal_access_tokens',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'permissions',
            'roles',
            'user_gifts',
            'gifts',
            'gift_categories',
            'competition_participants',
            'competitions',
            'challenges',
            'payments',
            'user_memberships',
            'membership_plans',
            'user_points',
            'reports',
            'chats',
            'swipes',
            'dashboard_items',
            'tokens',
            'user_profiles',
            'otps',
            'settings',
            'media',
            'users',
            'password_reset_tokens',
            'failed_jobs',
            'password_resets',
            'sessions',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a one-way migration
    }
}
