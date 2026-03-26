<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get or create test users
        $users = User::take(50)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $paymentMethods = ['stripe', 'paypal', 'razorpay', 'paytm'];
        $currencies = ['USD', 'EUR', 'GBP', 'INR'];
        $statuses = ['succeeded', 'pending', 'failed'];
        
        // Generate payments for the last 2 years
        $now = now();
        $startDate = $now->copy()->subYears(2);
        
        $this->command->info('Generating dummy payment data...');
        
        $bar = $this->command->getOutput()->createProgressBar(365 * 2); // 2 years of daily data
        
        while ($startDate->lte($now)) {
            // Generate 1-5 payments per day
            $paymentsPerDay = rand(1, 5);
            
            for ($i = 0; $i < $paymentsPerDay; $i++) {
                $user = $users->random();
                $amount = rand(100, 10000) / 100; // Random amount between 1.00 and 100.00
                $status = $statuses[array_rand($statuses)];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $currency = $currencies[array_rand($currencies)];
                
                // Create payment
                Payment::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'PYM' . strtoupper(Str::random(10)),
                    'amount' => $amount,
                    'currency' => $currency,
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                    'description' => 'Test payment ' . Str::random(5),
                    'metadata' => json_encode([
                        'billing_name' => $user->name,
                        'billing_email' => $user->email,
                        'plan' => 'Premium',
                    ]),
                    'created_at' => $startDate->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                    'updated_at' => now(),
                ]);
            }
            
            $startDate->addDay();
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine();
        $this->command->info('Successfully generated dummy payment data!');
    }
}
