<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection successful!\n";
    
    // Show current database
    $dbName = DB::select('SELECT DATABASE() as db')[0]->db;
    echo "📊 Current database: " . ($dbName ?: 'None selected') . "\n\n";
    
    // List all tables
    $tables = DB::select('SHOW TABLES');
    
    if (count($tables) === 0) {
        echo "❌ No tables found in the database.\n";
        exit(1);
    }
    
    echo "📋 Found " . count($tables) . " tables in the database:\n";
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "\n🔍 Table: $tableName\n";
        
        try {
            // Show table structure
            $columns = DB::select("SHOW COLUMNS FROM `$tableName`");
            echo "   Columns:\n";
            
            foreach ($columns as $column) {
                $column = (array)$column;
                echo "   - {$column['Field']} ({$column['Type']})\n";
            }
            
            // Show row count
            $count = DB::table($tableName)->count();
            echo "   📊 Row count: $count\n";
            
            // If it's the tokens table, show some sample data
            if (strtolower($tableName) === 'tokens') {
                $sample = DB::table($tableName)->first();
                echo "   Sample data: " . json_encode($sample, JSON_PRETTY_PRINT) . "\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Error reading table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "🔑 Please check your database credentials in the .env file.\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "💾 The database does not exist. Please create it first.\n";
    } elseif (strpos($e->getMessage(), 'SQLSTATE[HY000]') !== false) {
        echo "🔌 Database server is not running or not accessible.\n";
    }
    
    exit(1);
}
