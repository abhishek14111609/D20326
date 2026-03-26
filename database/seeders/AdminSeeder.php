<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (Admin::where('email', 'admin@admin.duos.com')->exists()) {
            echo "Default admin account already exists.\n";
            return;
        }

        // Create default admin account with all necessary fields
        $admin = Admin::create([
            'name' => 'DUOS Super Admin',
            'email' => 'admin@admin.duos.com',
            'password' => Hash::make('admin123'),
            'phone' => '+1234567890',
            'role' => 'super_admin',
            'status' => 'active',
            'profile_image' => null,
            'permissions' => json_encode([
                'users' => ['view', 'create', 'edit', 'delete', 'ban'],
                'settings' => ['view', 'edit'],
                'reports' => ['view', 'delete', 'resolve'],
                'content' => ['manage'],
                'analytics' => ['view'],
            ]),
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Output success message with login details
        echo "\n==================================================\n";
        echo "  DUOS ADMIN PANEL CREDENTIALS\n";
        echo "==================================================\n";
        echo "  Admin Panel URL: http://localhost:8000/admin\n";
        echo "  Email: admin@admin.duos.com\n";
        echo "  Password: admin123\n";
        echo "\n  !!! IMPORTANT: Change this password after first login !!!\n";
        echo "==================================================\n\n";
    }
}
