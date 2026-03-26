<?php

namespace Database\Seeders;

use App\Models\UserMembership;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserMembershipSeeder extends Seeder
{
    public function run()
    {
        $paymentMethods = ['credit_card', 'paypal', 'stripe', 'razorpay'];
        $statuses = ['active', 'cancelled', 'expired', 'pending'];
        
        // Get all user IDs
        $userIds = \App\Models\User::pluck('id')->toArray();
        
        // Create dummy memberships for each user
        foreach ($userIds as $userId) {
            // Create 1-3 memberships per user
            $membershipCount = rand(1, 3);
            
            for ($i = 0; $i < $membershipCount; $i++) {
                $startDate = Carbon::now()->subDays(rand(1, 60));
                $endDate = (clone $startDate)->addDays(rand(30, 365));
                $cancelledAt = rand(0, 1) ? (clone $endDate)->subDays(rand(1, 29)) : null;
                
                $status = $statuses[array_rand($statuses)];
                
                // Adjust status based on dates for realism
                if ($endDate->isPast()) {
                    $status = 'expired';
                } elseif ($status === 'expired' && $endDate->isFuture()) {
                    $status = 'active';
                }
                
                UserMembership::create([
                    'user_id' => $userId,
                    'membership_plan_id' => rand(1, 5), // Assuming 5 different plans
                    'starts_at' => $startDate,
                    'ends_at' => $endDate,
                    'cancelled_at' => $cancelledAt,
                    'status' => $status,
                    'auto_renew' => (bool)rand(0, 1),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'transaction_id' => 'TXN' . strtoupper(uniqid()),
                    'metadata' => json_encode([
                        'ip' => '192.168.1.' . rand(1, 255),
                        'device' => ['mobile', 'desktop', 'tablet'][rand(0, 2)],
                        'browser' => ['chrome', 'firefox', 'safari', 'edge'][rand(0, 3)]
                    ]),
                    'created_at' => $startDate,
                    'updated_at' => now(),
                ]);
            } // End of memberships loop for this user
        } // End of users loop
    }
}
