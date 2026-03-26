<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Truncating all table data...\n";

// Disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// List of tables to truncate
$tables = [
    'users',
    'user_profiles', 
    'tokens',
    'personal_access_tokens',
    'otps',
    'admins',
    'swipes',
    'chats',
    'gifts',
    'plans',
    'memberships',
    'leaderboards',
    'competitions',
    'competition_participants',
    'settings',
    'dashboard_items'
];

foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        DB::table($table)->truncate();
        echo "Truncated table: {$table} (had {$count} records)\n";
    } catch (Exception $e) {
        echo "Error truncating {$table}: " . $e->getMessage() . "\n";
    }
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\nAll tables truncated successfully!\n";

// Verify truncation
echo "\nVerification:\n";
echo "Users: " . DB::table('users')->count() . "\n";
echo "User Profiles: " . DB::table('user_profiles')->count() . "\n";
echo "Tokens: " . DB::table('tokens')->count() . "\n";
echo "Personal Access Tokens: " . DB::table('personal_access_tokens')->count() . "\n";
echo "OTPs: " . DB::table('otps')->count() . "\n";
echo "Admins: " . DB::table('admins')->count() . "\n";

echo "\nDatabase is now clean and ready for fresh testing!\n";
