<?php

namespace Database\Seeders;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first admin user to be the sender
        $adminUser = User::where('email', 'test@example.com')->first();
        
        if (!$adminUser) {
            $adminUser = User::first();
        }
        
        if (!$adminUser) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create a test receiver user if none exists
        $receiver = User::where('email', 'receiver@example.com')->first();
        
        if (!$receiver) {
            $receiver = User::factory()->create([
                'name' => 'Test Receiver',
                'email' => 'receiver@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $gifts = [
            [
                'name' => 'Rose',
                'description' => 'A beautiful red rose',
                'image_path' => 'gifts/rose.png',
                'price' => 10.00,
                'category_id' => 2, // Love
                'is_active' => true,
                'sender_id' => $adminUser->id,
                'receiver_id' => $receiver->id,
                'type' => 'gift',
                'context' => 'general',
            ],
            [
                'name' => 'Chocolate',
                'description' => 'Delicious box of chocolates',
                'image_path' => 'gifts/chocolate.png',
                'price' => 15.00,
                'category_id' => 2, // Love
                'is_active' => true,
                'sender_id' => $adminUser->id,
                'receiver_id' => $receiver->id,
                'type' => 'gift',
                'context' => 'general',
            ],
            [
                'name' => 'Teddy Bear',
                'description' => 'Cuddly teddy bear',
                'image_path' => 'gifts/teddy-bear.png',
                'price' => 20.00,
                'category_id' => 1, // Popular
                'is_active' => true,
                'sender_id' => $adminUser->id,
                'receiver_id' => $receiver->id,
                'type' => 'gift',
                'context' => 'general',
            ],
            [
                'name' => 'Diamond Ring',
                'description' => 'Shiny diamond ring',
                'image_path' => 'gifts/diamond-ring.png',
                'price' => 100.00,
                'category_id' => 5, // Premium
                'is_active' => true,
                'sender_id' => $adminUser->id,
                'receiver_id' => $receiver->id,
                'type' => 'gift',
                'context' => 'premium',
            ],
            [
                'name' => 'Balloon',
                'description' => 'Colorful balloon',
                'image_path' => 'gifts/balloon.png',
                'price' => 5.00,
                'category_id' => 3, // Celebration
                'is_active' => true,
                'sender_id' => $adminUser->id,
                'receiver_id' => $receiver->id,
                'type' => 'gift',
                'context' => 'celebration',
            ],
        ];

        foreach ($gifts as $gift) {
            // Only create if it doesn't already exist
            if (!Gift::where('name', $gift['name'])->exists()) {
                Gift::create($gift);
            }
        }
    }
}
