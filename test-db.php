<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illware\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "Database connection successful!\n";
    
    // Show current database
    $dbName = DB::select('SELECT DATABASE() as db')[0]->db;
    echo "Current database: " . ($dbName ?: 'None selected') . "\n";
    
    // List all tables
    $tables = DB::select('SHOW TABLES');
    echo "\nTables in database:\n";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $tableName = reset($table);
            echo "- $tableName\n";
            
            // Show table structure
            $columns = DB::select("DESCRIBE `$tableName`");
            foreach ($columns as $column) {
                echo "  - {$column->Field} ({$column->Type})\n";
            }
        }
    } else {
        echo "No tables found in database.\n";
    }
    
} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Please check your database credentials in the .env file.\n";
    }
}
