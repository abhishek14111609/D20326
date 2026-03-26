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
        // Mark the duplicate competition migrations as completed
        DB::table('migrations')->insert([
            ['migration' => '2025_08_12_054615_create_competitions_table', 'batch' => 1],
            ['migration' => '2025_08_12_060527_add_soft_deletes_to_competitions_table', 'batch' => 1],
            ['migration' => '2025_08_12_083500_update_competitions_table_columns', 'batch' => 1],
            ['migration' => '2025_08_12_084200_fix_competitions_table', 'batch' => 1],
            ['migration' => '2025_08_12_084500_fix_foreign_keys', 'batch' => 1],
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
                '2025_08_12_054615_create_competitions_table',
                '2025_08_12_060527_add_soft_deletes_to_competitions_table',
                '2025_08_12_083500_update_competitions_table_columns',
                '2025_08_12_084200_fix_competitions_table',
                '2025_08_12_084500_fix_foreign_keys'
            ])
            ->delete();
    }
};
