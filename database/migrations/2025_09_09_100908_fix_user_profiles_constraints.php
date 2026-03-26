<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // First, make sure we have the doctrine/dbal package for modifying columns
        if (!class_exists('Doctrine\DBAL\Driver')) {
            throw new \Exception('Please install doctrine/dbal package to modify columns. Run: composer require doctrine/dbal');
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            // First, check if the location column exists and modify it
            if (Schema::hasColumn('user_profiles', 'location')) {
                // Check if we can modify the column
                $connection = Schema::getConnection();
                $db = $connection->getDoctrineSchemaManager();
                $table = $db->listTableDetails('user_profiles');
                
                if ($table->hasColumn('location')) {
                    $column = $table->getColumn('location');
                    if ($column->getNotnull()) {
                        // If the column exists and is NOT NULL, modify it to be nullable
                        $table->modifyColumn('location', ['notnull' => false]);
                    }
                }
            }

            // List of all fields that should be nullable
            $fields = [
                'bio',
                'interest',
                'hobby',
                'relationship_status',
                'occupation',
                'languages',
                'location',
                'dob',
                'gender'
            ];

            // Make sure each field exists and is nullable
            foreach ($fields as $field) {
                if (Schema::hasColumn('user_profiles', $field)) {
                    // Get the column type
                    $columnType = Schema::getColumnType('user_profiles', $field);
                    
                    // Recreate the column with nullable
                    $table->{$columnType}($field)->nullable()->change();
                }
            }
        });
    }
    
    public function down()
    {
        // This is a one-way migration to fix constraints
        // We can't reliably reverse these changes without knowing the original state
    }
};
