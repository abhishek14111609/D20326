<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('duration_value');
            $table->enum('duration_unit', ['day', 'week', 'month', 'year']);
            $table->enum('level', ['free', 'premium', 'vip', 'enterprise'])->default('premium');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->json('features')->nullable();
            $table->string('stripe_plan_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->string('razorpay_plan_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['level', 'is_active']);
        });

        // Add default membership plans
        $this->seedDefaultPlans();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membership_plans');
    }

    /**
     * Seed default membership plans.
     *
     * @return void
     */
    protected function seedDefaultPlans()
    {
        $plans = [
            // Free plan
            [
                'name' => 'Free',
                'description' => 'Basic features to get you started',
                'price' => 0.00,
                'duration_value' => 1,
                'duration_unit' => 'month',
                'level' => 'free',
                'is_active' => true,
                'is_popular' => false,
                'features' => json_encode([
                    'Basic swiping',
                    'Limited likes per day',
                    'Basic profile',
                ]),
            ],
            
            // Monthly premium plan
            [
                'name' => 'Premium Monthly',
                'description' => 'Unlock all premium features',
                'price' => 9.99,
                'duration_value' => 1,
                'duration_unit' => 'month',
                'level' => 'premium',
                'is_active' => true,
                'is_popular' => true,
                'features' => json_encode([
                    'Unlimited likes',
                    'See who likes you',
                    'Advanced filters',
                    '5 super likes per week',
                    '1 free boost per month',
                ]),
            ],
            
            // Yearly premium plan (with discount)
            [
                'name' => 'Premium Yearly',
                'description' => 'Best value - Save 40%',
                'price' => 71.88, // $5.99/month equivalent
                'duration_value' => 1,
                'duration_unit' => 'year',
                'level' => 'premium',
                'is_active' => true,
                'is_popular' => true,
                'features' => json_encode([
                    'Everything in Premium Monthly',
                    'Save 40% compared to monthly',
                    'Priority customer support',
                ]),
            ],
            
            // VIP monthly plan
            [
                'name' => 'VIP Monthly',
                'description' => 'Exclusive VIP benefits',
                'price' => 24.99,
                'duration_value' => 1,
                'duration_unit' => 'month',
                'level' => 'vip',
                'is_active' => true,
                'is_popular' => false,
                'features' => json_encode([
                    'Everything in Premium',
                    'VIP badge on profile',
                    'Priority likes (get seen first)',
                    'Unlimited super likes',
                    '2 free boosts per month',
                    'Exclusive VIP events',
                ]),
            ],
            
            // VIP yearly plan (with discount)
            [
                'name' => 'VIP Yearly',
                'description' => 'Best VIP value - Save 50%',
                'price' => 149.88, // $12.49/month equivalent
                'duration_value' => 1,
                'duration_unit' => 'year',
                'level' => 'vip',
                'is_active' => true,
                'is_popular' => true,
                'features' => json_encode([
                    'Everything in VIP Monthly',
                    'Save 50% compared to monthly',
                    'Priority customer support',
                    'Exclusive VIP gifts',
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\MembershipPlan::create($plan);
        }
    }
};
