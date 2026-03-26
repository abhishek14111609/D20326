<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Services\MembershipService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MembershipController extends Controller
{
    protected $membershipService;

    public function __construct(MembershipService $membershipService)
    {
        $this->membershipService = $membershipService;
        $this->middleware('auth:sanctum');
    }

    /**
     * List plans
     */
    public function plans()
    {
        try {
            $plans = $this->membershipService->getAllMemberships();
            return response()->json(['status' => 'success', 'data' => $plans]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch plans: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch plans'], 500);
        }
    }

    /**
     * Subscribe
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'membership_plan_id' => ['required', 'integer', Rule::exists('membership_plans', 'id')->where(fn($q)=>$q->where('is_active',1))],
            'payment_method' => ['required','string',Rule::in(['stripe','paypal'])],
            'payment_token' => 'required|string|min:5',
            'coupon_code' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);

            $activeMembership = $this->membershipService->getUserMembership($user->id);
            if ($activeMembership) {
                DB::rollBack();
                return response()->json(['status'=>'error','message'=>'You already have active membership'],400);
            }

            $paymentResult = $this->membershipService->processPayment([
                'user'=>$user,'plan'=>$plan,
                'payment_method'=>$validated['payment_method'],
                'payment_token'=>$validated['payment_token'],
                'coupon_code'=>$validated['coupon_code'] ?? null,
            ]);

            if(!$paymentResult['success']){
                DB::rollBack();
                return response()->json(['status'=>'error','message'=>$paymentResult['message']],400);
            }

            $membership = $this->membershipService->subscribeUser($user->id, $plan->id, $validated['payment_method'], true);
            $membership->update([
                'transaction_id'=>$paymentResult['transaction_id'] ?? ($paymentResult['confirmation_id'] ?? null),
                'amount_paid'=>$paymentResult['amount'] ?? $plan->price,
                'currency'=>$paymentResult['currency'] ?? ($plan->currency ?? 'USD'),
                'metadata'=>json_encode($paymentResult['details'] ?? [])
            ]);

            DB::commit();
            return response()->json([
                'status'=>'success',
                'message'=>'Subscribed to '.$plan->name,
                'data'=>['membership'=>$membership->fresh()->load('plan')]
            ],201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription failed: '.$e->getMessage());
            return response()->json(['status'=>'error','message'=>'Subscription failed: '.$e->getMessage()],500);
        }
    }

    /**
     * Current membership
     */
    public function currentMembership()
    {
        $user = Auth::user();
        $membership = $this->membershipService->getUserMembership($user->id);
        if($membership){
            return response()->json(['status'=>'success','data'=>$membership,'has_active_membership'=>true]);
        }
        return response()->json(['status'=>'success','data'=>['plan'=>['name'=>'Free Tier','price'=>0]],'has_active_membership'=>false]);
    }

    /**
     * Cancel
     */
    public function cancelSubscription(Request $request)
    {
        $user = Auth::user();
        $membership = $this->membershipService->getUserMembership($user->id);
        if(!$membership) return response()->json(['status'=>'error','message'=>'No active subscription'],404);

        $membership->cancel(true);
        return response()->json(['status'=>'success','message'=>'Subscription cancelled','data'=>$membership]);
    }

    /**
     * Membership history
     */
    public function history()
    {
        $user = Auth::user();
        $memberships = UserMembership::with('plan')->where('user_id',$user->id)->orderBy('created_at','desc')->get();
        return response()->json(['status'=>'success','data'=>$memberships]);
    }
}
