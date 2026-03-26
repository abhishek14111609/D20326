<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\Competition;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Swipe;
use App\Models\User;
use App\Models\Gift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create test users
        $admin = User::where('email', 'test@example.com')->first();
        $users = User::where('email', '!=', 'test@example.com')->take(10)->get();
        
        if ($users->count() < 5) {
            $users = User::factory(10)->create();
            $users->push($admin);
        }

        // Create dummy competitions
        $competitions = [
            [
                'title' => 'Summer Photo Contest',
                'description' => 'Submit your best summer photos for a chance to win amazing prizes!',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'rules' => '1. Photos must be original\n2. Max 3 entries per user\n3. No offensive content',
                'prizes' => json_encode([
                    ['position' => 1, 'prize' => 'Professional Camera'],
                    ['position' => 2, 'prize' => 'Smartphone Gimbal'],
                    ['position' => 3, 'prize' => 'Photo Editing Software']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Coding Challenge 2025',
                'description' => 'Test your coding skills in this exciting challenge!',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(30),
                'rules' => '1. Code must be original\n2. No cheating\n3. Follow the given problem statement',
                'prizes' => json_encode([
                    ['position' => 1, 'prize' => 'MacBook Pro'],
                    ['position' => 2, 'prize' => 'Mechanical Keyboard'],
                    ['position' => 3, 'prize' => 'Premium IDE Subscription']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($competitions as $competitionData) {
            Competition::updateOrCreate(
                ['title' => $competitionData['title']],
                $competitionData
            );
        }

        // Create dummy challenges
        $challenges = [
            [
                'title' => '30-Day Fitness Challenge',
                'description' => 'Complete daily fitness activities for 30 days',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'rules' => '1. Complete daily tasks\n2. Post proof\n3. No cheating',
                'reward_points' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Reading Challenge',
                'description' => 'Read 10 books in 3 months',
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'rules' => '1. Books must be 100+ pages\n2. Write a short review\n3. Share your progress',
                'reward_points' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($challenges as $challengeData) {
            Challenge::updateOrCreate(
                ['title' => $challengeData['title']],
                $challengeData
            );
        }

        // Get existing plans from the database
        $plans = DB::table('plans')->get();
        
        if ($plans->isEmpty()) {
            $this->command->warn('No plans found in the database. Please run the PlansTableSeeder first.');
            return;
        }
        
        // Create user memberships for some users
        foreach ($users->take(3) as $user) {
            $plan = $plans->random();
            $purchaseDate = now()->subDays(rand(1, 30));
            $expiryDate = (clone $purchaseDate)->add(1, 'month'); // Default to 1 month
            
            // For free plan, set expiry to 7 days
            if ($plan->price == 0) {
                $expiryDate = (clone $purchaseDate)->addDays(7);
            }
            
            // Create membership if it doesn't exist
            if (!DB::table('memberships')->where('user_id', $user->id)->where('plan_id', $plan->id)->exists()) {
                DB::table('memberships')->insert([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'transaction_id' => 'TXN' . strtoupper(uniqid()),
                    'purchase_date' => $purchaseDate,
                    'expiry_date' => $expiryDate,
                    'platform' => ['web', 'ios', 'android'][array_rand(['web', 'ios', 'android'])],
                    'status' => $expiryDate->isFuture() ? 'active' : 'expired',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create dummy payments
        $paymentMethods = ['credit_card', 'paypal', 'stripe', 'bank_transfer'];
        $statuses = ['completed', 'pending', 'failed'];
        
        foreach ($users as $user) {
            Payment::create([
                'user_id' => $user->id,
                'amount' => rand(500, 5000) / 100,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'status' => $statuses[array_rand($statuses)],
                'transaction_id' => 'TXN' . strtoupper(uniqid()),
                'description' => 'Membership payment',
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now(),
            ]);
        }

        // Create dummy swipes
        $swipeTypes = ['like', 'dislike', 'superlike'];
        
        foreach ($users as $swiper) {
            $swipees = $users->where('id', '!=', $swiper->id)->random(3);
            
            foreach ($swipees as $swipee) {
                Swipe::updateOrCreate(
                    [
                        'swiper_id' => $swiper->id,
                        'swiped_id' => $swipee->id,
                    ],
                    [
                        'type' => $swipeTypes[array_rand($swipeTypes)],
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
