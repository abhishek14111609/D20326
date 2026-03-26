<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic',
                'price' => 9.99,
                'duration_days' => 30,
                'features' => json_encode([
                    'max_stores' => 1,
                    'storage_limit' => 10.00,
                    'max_products' => 100,
                    'description' => 'Basic plan with essential features',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium',
                'price' => 29.99,
                'duration_days' => 30,
                'features' => json_encode([
                    'max_stores' => 5,
                    'storage_limit' => 50.00,
                    'max_products' => 1000,
                    'description' => 'Premium plan with advanced features',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Free',
                'price' => 0.00,
                'duration_days' => 7,
                'features' => json_encode([
                    'max_stores' => 1,
                    'storage_limit' => 1.00,
                    'max_products' => 10,
                    'description' => 'Free plan with basic features',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert plans if they don't exist
        foreach ($plans as $plan) {
            if (!DB::table('plans')->where('name', $plan['name'])->exists()) {
                DB::table('plans')->insert($plan);
            }
        }

        $this->command->info('Plans table seeded!');
    }
}
