<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create or update test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'), // Ensure a known password for testing
                'email_verified_at' => now(),
            ]
        );

        // Seed roles and permissions
        $this->call([
            RoleAndPermissionSeeder::class,
            UserMembershipSeeder::class,
        ]);

        // Seed gift categories if they don't exist
        if (\DB::table('gift_categories')->count() === 0) {
            $this->call([
                \Database\Seeders\GiftCategorySeeder::class,
            ]);
        }

        // Seed gifts
        $this->call([
            \Database\Seeders\GiftSeeder::class,
        ]);
        
        // Seed dummy data
        $this->call([
            \Database\Seeders\DummyDataSeeder::class,
        ]);

        // Seed payment
        $this->call([
            \Database\Seeders\PaymentSeeder::class,
        ]);

        // Seed test call
        $this->call([
            \Database\Seeders\TestCallSeeder::class,
        ]);
    }
}
