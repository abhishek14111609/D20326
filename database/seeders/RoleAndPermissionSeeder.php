<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view settings',
            'edit settings',
            'view reports',
            'view analytics',
            'manage content',
            'manage subscriptions',
            'manage gifts',
            'manage competitions',
            'manage leaderboards',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create roles and assign created permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'admin']);
        $supportRole = Role::firstOrCreate(['name' => 'support', 'guard_name' => 'admin']);

        // Super Admin has all permissions
        $superAdminRole->syncPermissions(Permission::all());

        // Admin has most permissions except user management
        $adminRole->syncPermissions([
            'view dashboard',
            'view users',
            'edit users',
            'view settings',
            'view reports',
            'view analytics',
            'manage content',
            'manage gifts',
            'manage competitions',
            'manage leaderboards',
        ]);

        // Moderator has limited permissions
        $moderatorRole->syncPermissions([
            'view dashboard',
            'view users',
            'view reports',
            'manage content',
        ]);

        // Support role has very limited permissions
        $supportRole->syncPermissions([
            'view dashboard',
            'view users',
        ]);

        // Create super admin user if not exists
        $superAdmin = Admin::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }
    }
}
