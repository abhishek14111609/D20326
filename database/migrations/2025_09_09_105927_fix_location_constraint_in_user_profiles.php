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
    public function up()
    {
        $fields = ['interest', 'hobby'];
        
        foreach ($fields as $field) {
            try {
                // Try to drop foreign key constraint
                DB::statement("
                    ALTER TABLE `user_profiles` 
                    DROP FOREIGN KEY IF EXISTS `user_profiles.{$field}`
                ");
    
                // Try to drop index
                DB::statement("
                    ALTER TABLE `user_profiles` 
                    DROP INDEX IF EXISTS `user_profiles.{$field}`
                ");
    
                // Modify column to be nullable
                DB::statement("
                    ALTER TABLE `user_profiles` 
                    MODIFY COLUMN `{$field}` TEXT NULL
                ");
            } catch (\Exception $e) {
                // If above fails, try with foreign key checks disabled
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::statement("
                    ALTER TABLE `user_profiles` 
                    MODIFY COLUMN `{$field}` TEXT NULL
                ");
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }
    }
    
    public function down()
    {
        // This is a one-way migration
    }
};