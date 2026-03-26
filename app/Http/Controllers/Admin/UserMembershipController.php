<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserMembership;
use App\Models\User;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserMembershipController extends Controller
{
    /**
     * Display a listing of user memberships.
     */
    public function index(Request $request)
    {
        $query = UserMembership::with(['user', 'plan'])
            ->when($request->filled('search'), function($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', $search)
                      ->orWhere('email', 'like', $search);
                })->orWhereHas('plan', function($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            })
            ->when($request->filled('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('plan_id'), function($q) use ($request) {
                $q->where('membership_plan_id', $request->plan_id);
            })
            ->when($request->filled('date_from'), function($q) use ($request) {
                $q->whereDate('starts_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                $q->whereDate('ends_at', '<=', $request->date_to);
            });
            
        $memberships = $query->latest()->paginate(15);
        $plans = MembershipPlan::pluck('name', 'id');
        $statuses = [
            'active' => 'Active',
            'cancelling' => 'Cancelling',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            'paused' => 'Paused',
            'payment_failed' => 'Payment Failed',
        ];
            
        return view('admin.pages.user-memberships.index', compact('memberships', 'plans', 'statuses'));
    }

    /**
     * Show the form for creating a new user membership.
     */
    public function create()
    {
        $users = User::active()->pluck('name', 'id');
        $plans = MembershipPlan::active()->pluck('name', 'id');
        
        return view('admin.pages.user-memberships.create', compact('users', 'plans'));
    }

    /**
     * Store a newly created user membership in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'starts_at' => 'required|date',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month,year',
            'auto_renew' => 'boolean',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
        ]);
        
        $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
        
        $membership = new UserMembership();
        $membership->user_id = $validated['user_id'];
        $membership->membership_plan_id = $validated['membership_plan_id'];
        $membership->starts_at = $validated['starts_at'];
        $membership->ends_at = Carbon::parse($validated['starts_at'])
            ->add($validated['duration_value'], $validated['duration_unit']);
        $membership->status = 'active';
        $membership->auto_renew = $request->has('auto_renew');
        $membership->payment_method = $validated['payment_method'] ?? 'admin';
        $membership->transaction_id = $validated['transaction_id'];
        $membership->save();
        
        return redirect()
            ->route('admin.user-memberships.index')
            ->with('success', 'Membership assigned successfully.');
    }

    /**
     * Display the specified user membership.
     */
    public function show($id)
    {
        $membership = UserMembership::with(['user', 'plan'])->findOrFail($id);
        return view('admin.pages.user-memberships.show', compact('membership'));
    }

    /**
     * Show the form for editing the specified user membership.
     */
    public function edit($id)
    {
        $membership = UserMembership::findOrFail($id);
        $users = User::active()->pluck('name', 'id');
        $plans = MembershipPlan::active()->pluck('name', 'id');
        
        return view('admin.pages.user-memberships.edit', compact('membership', 'users', 'plans'));
    }

    /**
     * Update the specified user membership in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'required|in:active,cancelling,cancelled,expired,paused,payment_failed',
            'auto_renew' => 'boolean',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
        ]);
        
        $membership = UserMembership::findOrFail($id);
        $membership->update($validated);
        
        return redirect()
            ->route('admin.user-memberships.index')
            ->with('success', 'Membership updated successfully.');
    }

    /**
     * Remove the specified user membership from storage.
     */
    public function destroy($id)
    {
        $membership = UserMembership::findOrFail($id);
        $membership->delete();
        
        return redirect()
            ->route('admin.user-memberships.index')
            ->with('success', 'Membership deleted successfully.');
    }
}
