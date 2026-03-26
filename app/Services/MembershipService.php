<?php

namespace App\Services;

use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MembershipService
{
    /**
     * Get all active memberships
     */
    public function getAllMemberships()
    {
        return MembershipPlan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();
    }

    /**
     * Get user's active membership
     */
    public function getUserMembership($userId)
    {
        return UserMembership::with('plan')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Subscribe user to a plan
     */
    public function subscribeUser($userId, $planId, $paymentMethod, $autoRenew = true)
    {
        return DB::transaction(function () use ($userId, $planId, $paymentMethod, $autoRenew) {
            $plan = MembershipPlan::findOrFail($planId);
            $now = now();
            $endDate = $now->copy()->add($plan->duration_value, $plan->duration_unit);

            // Cancel existing membership
            $this->cancelSubscription($userId, true);

            $userMembership = UserMembership::create([
                'user_id' => $userId,
                'membership_plan_id' => $plan->id,
                'starts_at' => $now,
                'ends_at' => $endDate,
                'status' => 'active',
                'auto_renew' => $autoRenew,
                'payment_method' => $paymentMethod,
            ]);

            // Update user's membership level
            $user = User::find($userId);
            $user->membership_level = $plan->level;
            $user->save();

            return $userMembership->load('plan');
        });
    }

    /**
     * Cancel user's subscription
     */
    public function cancelSubscription($userId, $immediate = false)
    {
        $membership = $this->getUserMembership($userId);
        if (!$membership) return false;

        $updates = [
            'status' => $immediate ? 'cancelled' : 'cancelling',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ];
        if ($immediate) $updates['ends_at'] = now();

        $membership->update($updates);

        // Reset user level if immediate
        if ($immediate) {
            $membership->user->membership_level = 'free';
            $membership->user->save();
        }

        return true;
    }

    /**
     * Process payment
     */
    public function processPayment(array $data): array
    {
        $user = $data['user'];
        $plan = $data['plan'];
        $paymentMethod = $data['payment_method'];
        $paymentToken = $data['payment_token'];
        $couponCode = $data['coupon_code'] ?? null;

        try {
            switch (strtolower($paymentMethod)) {
                case 'stripe':
                    return $this->processStripePayment($user, $plan, $paymentToken, $couponCode);
                case 'paypal':
                    return $this->processPayPalPayment($user, $plan, $paymentToken, $couponCode);
                default:
                    throw new \Exception("Unsupported payment method: {$paymentMethod}");
            }
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
                'transaction_id' => null,
                'amount' => $plan->price,
                'currency' => 'USD',
            ];
        }
    }

    /**
     * Stripe payment (SCA safe)
     */
    protected function processStripePayment($user, $plan, $paymentToken, $couponCode = null): array
    {
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => ['user_id' => $user->id, 'plan_id' => $plan->id]
            ]);

            $paymentIntent = \Stripe\PaymentIntent::create([
				'amount' => $plan->price * 100,
				'currency' => 'usd',
				'customer' => $customer->id,
				'description' => 'Membership: ' . $plan->name,
				'metadata' => [
					'plan_name' => $plan->name,
					'user_id' => $user->id,
					'coupon_code' => $couponCode
				],
				'automatic_payment_methods' => ['enabled' => true],
			]);

		// 🔹 Record payment in `payments` table (pending initially)
        $payment = \App\Models\Payment::create([
            'user_id' => $user->id,
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $plan->price,
            'currency' => 'USD',
            'payment_method' => 'stripe',
			'transaction_id' => $paymentIntent->id,
            'status' => 'pending',
            'description' => 'Membership: ' . $plan->name,
            'metadata' => [
                'plan_id' => $plan->id,
                'coupon_code' => $couponCode,
                'raw_payment_intent' => $paymentIntent->toArray()
            ]
        ]);

        return [
            'success' => true,
            'message' => 'Payment initiated',
            'payment_intent_id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
			'transaction_id' => $paymentIntent->id,
			'details' => $paymentIntent->toArray(),
			'confirmation_id' => 'stripe_' . $paymentIntent->id,
            'amount' => $plan->price,
            'currency' => $paymentIntent->currency,
            'payment_model_id' => $payment->id,
            'raw_payment_intent' => $paymentIntent->toArray(),
        ];
			
        } catch (\Exception $e) {
            throw new \Exception('Stripe payment failed: ' . $e->getMessage());
        }
    }

    /**
     * PayPal payment (placeholder)
     */
    protected function processPayPalPayment($user, $plan, $paymentToken, $couponCode = null): array
    {
        return [
            'success' => true,
            'message' => 'PayPal payment processed successfully',
            'transaction_id' => 'paypal_' . uniqid(),
            'amount' => $plan->price,
            'currency' => 'USD',
            'details' => ['payment_method' => 'paypal', 'user_id' => $user->id, 'plan_id' => $plan->id],
            'confirmation_id' => 'paypal_' . uniqid()
        ];
    }
}
