<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    /**
     * Display a listing of the membership plans.
     */
    public function index()
    {
        $plans = MembershipPlan::latest()->paginate(10);
        return view('admin.pages.membership-plans.index', compact('plans'));
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    
    /**
     * Show the form for creating a new membership plan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $durations = [
            'day' => 'Day(s)',
            'week' => 'Week(s)',
            'month' => 'Month(s)',
            'year' => 'Year(s)'
        ];
        
        return view('admin.pages.membership-plans.create', compact('durations'));
    }
    
    /**
     * Display a list of users who have purchased memberships
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userPurchases(Request $request)
    {
        $query = User::withCount(['userMemberships as active_memberships' => function($q) {
                $q->where('status', 'active')
                  ->where('ends_at', '>=', now());
            }])
            ->with(['userMemberships.plan'])
            ->whereHas('userMemberships')
            ->when($request->filled('search'), function($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            })
            ->when($request->filled('plan_id'), function($q) use ($request) {
                $q->whereHas('userMemberships', function($q) use ($request) {
                    $q->where('membership_plan_id', $request->plan_id);
                });
            })
            ->when($request->filled('status'), function($q) use ($request) {
                $q->whereHas('userMemberships', function($q) use ($request) {
                    if ($request->status === 'active') {
                        $q->where('status', 'active')
                          ->where('ends_at', '>=', now());
                    } else if ($request->status === 'expired') {
                        $q->where('status', 'active')
                          ->where('ends_at', '<', now());
                    } else {
                        $q->where('status', $request->status);
                    }
                });
            });
            
        $users = $query->latest()->paginate(15);
        $plans = MembershipPlan::pluck('name', 'id');
        
        return view('admin.pages.memberships.user-purchases', compact('users', 'plans'));
    }

    /**
     * Store a newly created membership plan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month,year',
            'level' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'stripe_plan_id' => 'nullable|string|max:255',
            'paypal_plan_id' => 'nullable|string|max:255',
            'razorpay_plan_id' => 'nullable|string|max:255',
        ]);

        // Clean up features array (remove empty values)
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features'], function ($feature) {
                return is_string($feature) && trim($feature) !== '';
            }));
        }

        // Generate a slug if not provided
        $validated['slug'] = Str::slug($validated['name']);

        $membershipPlan = MembershipPlan::create($validated);

        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Membership plan created successfully.');
    }

    /**
     * Display the specified membership plan.
     */
    public function show(MembershipPlan $membership)
    {
        return view('admin.pages.membership-plans.show', compact('membership'));
    }

    /**
     * Show the form for editing the specified membership plan.
     */
    public function edit(MembershipPlan $membership)
    {
        $durations = [
            'day' => 'Day(s)',
            'week' => 'Week(s)',
            'month' => 'Month(s)',
            'year' => 'Year(s)'
        ];
        
        return view('admin.pages.membership-plans.edit', compact('membership', 'durations'));
    }

    /**
     * Update the specified membership plan in storage.
     */
    public function update(Request $request, MembershipPlan $membership)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month,year',
            'level' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'stripe_plan_id' => 'nullable|string|max:255',
            'paypal_plan_id' => 'nullable|string|max:255',
            'razorpay_plan_id' => 'nullable|string|max:255',
        ]);

        // Clean up features array (remove empty values)
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features'], function ($feature) {
                return is_string($feature) && trim($feature) !== '';
            }));
        } else {
            $validated['features'] = [];
        }

        // Generate a slug if name was changed
        if ($membership->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $membership->update($validated);

        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Membership plan updated successfully.');
    }

    /**
     * Remove the specified membership plan from storage.
     */
    public function destroy(MembershipPlan $membership)
    {
        $membership->delete();
        
        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Membership plan deleted successfully.');
    }
}
