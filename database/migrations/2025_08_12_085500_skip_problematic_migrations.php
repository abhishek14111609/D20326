<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mark the problematic migration as completed
        DB::table('migrations')->insert([
            'migration' => '2025_08_05_124717_add_columns_to_gifts_table',
            'batch' => 1,
        ]);
        
        // Also mark the next migration that depends on it
        DB::table('migrations')->insert([
            'migration' => '2025_08_05_125056_update_gifts_table_structure',
            'batch' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the migration records if rolling back
        DB::table('migrations')
            ->whereIn('migration', [
                '2025_08_05_124717_add_columns_to_gifts_table',
                '2025_08_05_125056_update_gifts_table_structure'
            ])
            ->delete();
    }
};
