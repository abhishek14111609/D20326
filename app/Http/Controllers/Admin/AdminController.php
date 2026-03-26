<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Chat;
use App\Models\Swipe;
use App\Models\Membership;
use App\Models\Gift;
use App\Models\Payment;
use App\Models\UserProfile;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Display the admin dashboard with analytics
     */
    public function dashboard()
    {
        // Get current year and month
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $years = range($currentYear - 4, $currentYear);
        
        // Debug: Check if payments table exists and has data
        $hasPayments = DB::table('payments')->exists();
        $paymentCount = $hasPayments ? DB::table('payments')->count() : 0;
        
        // Log payment data for debugging
        \Log::info('Payment data check:', [
            'table_exists' => $hasPayments,
            'payment_count' => $paymentCount,
            'sample_payments' => $hasPayments ? DB::table('payments')->select('id', 'user_id', 'amount', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->toArray() : []
        ]);

        // Get payment data for the current year (grouped by month)
        $monthlyPayments = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(DISTINCT user_id) as active_users'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(CASE WHEN status = "succeeded" THEN amount ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN status = "succeeded" AND payment_method != "refund" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN payment_method = "refund" THEN amount ELSE 0 END) as expenses')
            )
            ->whereYear('created_at', $currentYear)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Initialize monthly data with zeros
        $monthlyData = array_fill(0, 12, [
            'active_users' => 0,
            'transaction_count' => 0,
            'total_revenue' => 0,
            'income' => 0,
            'expenses' => 0,
            'profit' => 0
        ]);

        // Fill in actual data
        foreach ($monthlyPayments as $payment) {
            $monthIndex = $payment->month - 1;
            $monthlyData[$monthIndex] = [
                'active_users' => (int)$payment->active_users,
                'transaction_count' => (int)$payment->transaction_count,
                'total_revenue' => (float)$payment->total_revenue,
                'income' => (float)$payment->income,
                'expenses' => (float)$payment->expenses,
                'profit' => (float)($payment->income - $payment->expenses)
            ];
        }

        // Get payment data for the last 5 years (grouped by year)
        $yearlyPayments = Payment::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(DISTINCT user_id) as active_users'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(CASE WHEN status = "succeeded" THEN amount ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN status = "succeeded" AND payment_method != "refund" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN payment_method = "refund" THEN amount ELSE 0 END) as expenses')
            )
            ->whereIn(DB::raw('YEAR(created_at)'), $years)
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Initialize yearly data with zeros
        $yearlyData = [];
        foreach ($years as $year) {
            $yearlyData[$year] = [
                'active_users' => 0,
                'transaction_count' => 0,
                'total_revenue' => 0,
                'income' => 0,
                'expenses' => 0,
                'profit' => 0
            ];
        }

        // Fill in actual data
        foreach ($yearlyPayments as $payment) {
            $yearlyData[$payment->year] = [
                'active_users' => (int)$payment->active_users,
                'transaction_count' => (int)$payment->transaction_count,
                'total_revenue' => (float)$payment->total_revenue,
                'income' => (float)$payment->income,
                'expenses' => (float)$payment->expenses,
                'profit' => (float)($payment->income - $payment->expenses)
            ];
        }

        // Get user growth data
        $monthlyUserGrowth = User::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as user_count')
            )
            ->whereYear('created_at', $currentYear)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Initialize monthly user data with zeros
        $monthlyUserData = array_fill(0, 12, [
            'new_users' => 0,
            'total_users' => 0
        ]);
        
        // Fill in actual user data
        $cumulativeUsers = User::whereYear('created_at', '<', $currentYear)->count();
        foreach ($monthlyUserGrowth as $userData) {
            $monthIndex = $userData->month - 1;
            $monthlyUserData[$monthIndex]['new_users'] = (int)$userData->user_count;
            $cumulativeUsers += (int)$userData->user_count;
            $monthlyUserData[$monthIndex]['total_users'] = $cumulativeUsers;
        }

        // Calculate cumulative users for the year
        $cumulative = 0;
        $cumulativeUserData = array_map(function($month) use (&$cumulative) {
            $cumulative += $month['new_users'];
            return $cumulative;
        }, $monthlyUserData);

        // Get yearly user growth
        $yearlyUserGrowth = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as user_count')
            )
            ->whereIn(DB::raw('YEAR(created_at)'), $years)
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Initialize yearly user data
        $yearlyUserData = [];
        $cumulativeYearlyUsers = 0;
        foreach ($years as $year) {
            $yearData = $yearlyUserGrowth->firstWhere('year', $year);
            $count = $yearData ? (int)$yearData->user_count : 0;
            $cumulativeYearlyUsers += $count;
            $yearlyUserData[$year] = [
                'new_users' => $count,
                'total_users' => $cumulativeYearlyUsers
            ];
        }

        // Get payment status counts
        $paymentStatusCounts = Payment::select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get recent payments with user data
        $recentPayments = Payment::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Get gender distribution
        $genderDistribution = UserProfile::select(
                'gender',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        // Get stats for the dashboard
        $thirtyDaysAgo = now()->subDays(30);
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where(function($query) use ($thirtyDaysAgo) {
                $query->where('last_seen', '>=', $thirtyDaysAgo)
                      ->orWhere('updated_at', '>=', $thirtyDaysAgo);
            })->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'total_revenue' => Payment::where('status', 'succeeded')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'succeeded')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'male_users' => $genderDistribution['male'] ?? 0,
            'female_users' => $genderDistribution['female'] ?? 0,
            'other_gender' => $genderDistribution['other'] ?? 0,
            'total_transactions' => Payment::count(),
            'pending_transactions' => Payment::where('status', 'pending')->count(),
            'successful_transactions' => Payment::where('status', 'succeeded')->count(),
            'failed_transactions' => Payment::where('status', 'failed')->count(),
            'recent_payments' => $recentPayments, // Add recent payments to stats
            'successful_payments' => Payment::where('status', 'succeeded')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
            'paying_users' => Payment::where('status', 'succeeded')
                ->distinct('user_id')
                ->count('user_id'),
            'inactive_users' => User::where('last_seen', '<', now()->subDays(30))->count(),
            'new_active_users_this_month' => User::where('last_seen', '>=', now()->subDays(30))
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // Prepare the view data
        $viewData = [
            'stats' => $stats,
            'monthlyData' => $monthlyData,
            'yearlyData' => $yearlyData,
            'monthlyUserData' => $monthlyUserData,
            'yearlyUserData' => $yearlyUserData,
            'paymentStatusCounts' => $paymentStatusCounts,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'years' => $years,
            'totalRevenue' => $stats['total_revenue'],
            'monthlyRevenue' => $stats['monthly_revenue']
        ];

        return view('admin.pages.dashboard', $viewData);
    }

    /**
     * Display users management
     */
    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Display swipes/matches management
     */
    public function swipes()
    {
        $swipes = Swipe::with(['swiper', 'swiped'])->latest()->paginate(20);
        return view('admin.swipes.index', compact('swipes'));
    }

    /**
     * Display the specified swipe.
     *
     * @param  \App\Models\Swipe  $swipe
     * @return \Illuminate\View\View
     */
    public function showSwipe(Swipe $swipe)
    {
        $swipe->load(['swiper', 'swiped']);
        
        $activities = [
            'swiped_at' => $swipe->created_at->format('M d, Y h:i A'),
            'matched' => $swipe->matched ? 'Yes' : 'No',
            'match_date' => $swipe->matched ? $swipe->updated_at->format('M d, Y h:i A') : 'N/A',
        ];
        
        return view('admin.swipes.show', compact('swipe', 'activities'));
    }

    /**
     * Remove the specified swipe from storage.
     *
     * @param  \App\Models\Swipe  $swipe
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSwipe(Swipe $swipe)
    {
        $swipe->delete();
        
        return redirect()->route('admin.swipes.index')
            ->with('success', 'Swipe deleted successfully');
    }

    /**
     * Mark a swipe as matched
     *
     * @param  \App\Models\Swipe  $swipe
     * @return \Illuminate\Http\Response
     */
    public function matchSwipe(Swipe $swipe)
    {
        try {
            $swipe->update(['matched' => true]);
            return redirect()->back()->with('success', 'Swipe marked as matched successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update swipe status: ' . $e->getMessage());
        }
    }

    /**
     * Mark a swipe as not matched
     *
     * @param  \App\Models\Swipe  $swipe
     * @return \Illuminate\Http\Response
     */
    public function unmatchSwipe(Swipe $swipe)
    {
        try {
            $swipe->update(['matched' => false]);
            return redirect()->back()->with('success', 'Swipe marked as not matched.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update swipe status: ' . $e->getMessage());
        }
    }

    /**
     * Display chats management
     */
    public function chats()
    {
        $chats = Chat::with(['sender', 'receiver'])->latest()->paginate(20);
        return view('admin.chats.index', compact('chats'));
    }

    /**
     * Display memberships management
     */
    public function memberships()
    {
        $memberships = Membership::with(['user', 'plan'])->latest()->paginate(20);
        return view('admin.memberships.index', compact('memberships'));
    }

    /**
     * Display leaderboard
     */
    public function leaderboard()
    {
        $users = User::withCount(['sentSwipes', 'receivedSwipes', 'sentMessages'])
            ->orderBy('created_at', 'desc') // Temporarily sort by creation date
            ->paginate(10);
            
        return view('admin.leaderboard.index', compact('users'));
    }

    /**
     * Display user reports
     */
    public function userReports()
    {
        $reports = [
            'total_users' => User::count(),
            'active_today' => User::whereDate('last_seen', today())->count(),
            'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'premium_users' => User::whereHas('memberships', function($q) {
                $q->where('status', 'active');
            })->count(),
        ];
        
        return view('admin.reports.users', compact('reports'));
    }

    /**
     * Display system reports
     */
    public function systemReports()
    {
        $reports = [
            'total_matches' => Swipe::where('matched', true)->count(),
            'messages_today' => Chat::whereDate('created_at', today())->count(),
            'active_sessions' => User::where('last_seen', '>=', now()->subMinutes(5))->count(),
            'storage_usage' => $this->getStorageUsage(),
        ];
        
        return view('admin.reports.system', compact('reports'));
    }

    /**
     * Display gifts management
     */
    public function gifts()
    {
        $gifts = Gift::latest()->paginate(20);
        return view('admin.gifts.index', compact('gifts'));
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function showUser(User $user)
    {
        // Load user relationships if needed
        $user->loadCount(['sentSwipes', 'receivedSwipes', 'sentMessages', 'receivedMessages', 'memberships', 'payments']);
        
        // Get user's recent activities
        $recentActivities = [
            'last_login' => $user->last_seen ? $user->last_seen->diffForHumans() : 'Never',
            'joined_date' => $user->created_at->format('M d, Y'),
            'total_matches' => $user->sentSwipes()->where('matched', true)->count() + 
                             $user->receivedSwipes()->where('matched', true)->count(),
        ];
        
        // Get user's subscription status
        $subscription = $user->memberships()->latest()->first();
        
        return view('admin.users.show', compact('user', 'recentActivities', 'subscription'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'status' => 'required|in:active,inactive,banned',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function deleteUser(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    /**
     * Show the form for creating a new gift
     */
    public function createGift()
    {
        return view('admin.gifts._form');
    }

    /**
     * Store a newly created gift in storage
     */
    public function storeGift(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $gift = Gift::create($validated);
        
        if ($request->hasFile('image')) {
            $gift->addMediaFromRequest('image')->toMediaCollection('gifts');
        }

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift created successfully');
    }

    /**
     * Show the form for editing the specified gift
     */
    public function editGift(Gift $gift)
    {
        return view('admin.gifts.edit', compact('gift'));
    }

    /**
     * Update the specified gift in storage
     */
    public function updateGift(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $gift->update($validated);
        
        if ($request->hasFile('image')) {
            $gift->clearMediaCollection('gifts');
            $gift->addMediaFromRequest('image')->toMediaCollection('gifts');
        }

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift updated successfully');
    }

    /**
     * Remove the specified gift from storage
     */
    public function deleteGift(Gift $gift)
    {
        $gift->delete();
        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift deleted successfully');
    }

    /**
     * Display system settings page
     */
    public function settings()
    {
        // Get all settings from the database
        $dbSettings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
        
        // Define default settings with fallbacks
        $settings = [
            'site_name' => $dbSettings['site_name'] ?? config('app.name'),
            'site_email' => $dbSettings['site_email'] ?? config('mail.from.address'),
            'maintenance_mode' => $dbSettings['maintenance_mode'] ?? config('app.maintenance_mode', false),
            'registration_enabled' => $dbSettings['registration_enabled'] ?? config('app.registration_enabled', true),
            'default_user_role' => $dbSettings['default_user_role'] ?? config('app.default_user_role', 'user'),
            'google_maps_api_key' => $dbSettings['google_maps_api_key'] ?? '',
            'video_call_api_key' => $dbSettings['video_call_api_key'] ?? '',
            'audio_call_api_key' => $dbSettings['audio_call_api_key'] ?? '',
            'enable_push_notifications' => $dbSettings['enable_push_notifications'] ?? true,
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|string|email|max:255',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'default_user_role' => 'required|string|in:user,premium,admin',
            'google_maps_api_key' => 'nullable|string',
            'video_call_api_key' => 'nullable|string',
            'audio_call_api_key' => 'nullable|string',
            'enable_push_notifications' => 'boolean',
        ]);

        // Update settings in the database using the Setting model
        $settings = [
            'site_name' => $validated['site_name'],
            'site_email' => $validated['site_email'],
            'maintenance_mode' => $request->has('maintenance_mode') ? 1 : 0,
            'registration_enabled' => $request->has('registration_enabled') ? 1 : 0,
            'default_user_role' => $validated['default_user_role'],
            'google_maps_api_key' => $validated['google_maps_api_key'] ?? '',
            'video_call_api_key' => $validated['video_call_api_key'] ?? '',
            'audio_call_api_key' => $validated['audio_call_api_key'] ?? '',
            'enable_push_notifications' => $request->has('enable_push_notifications') ? 1 : 0,
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'string']
            );
        }

        // Update config values for the current request
        config([
            'app.name' => $validated['site_name'],
            'mail.from.address' => $validated['site_email'],
            'app.maintenance_mode' => $request->has('maintenance_mode'),
            'app.registration_enabled' => $request->has('registration_enabled'),
            'app.default_user_role' => $validated['default_user_role'],
            'services.google.maps.key' => $validated['google_maps_api_key'] ?? null,
            'services.video_call.key' => $validated['video_call_api_key'] ?? null,
            'services.audio_call.key' => $validated['audio_call_api_key'] ?? null,
            'services.push_notifications.enabled' => $request->has('enable_push_notifications'),
        ]);
        
        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Display account settings page
     */
    public function accountSettings()
    {
        $admin = auth('admin')->user();
        return view('admin.account.settings', compact('admin'));
    }

    /**
     * Update account settings
     */
    public function updateAccountSettings(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'current_password' => 'nullable|required_with:new_password|string|current_password:admin',
            'new_password' => 'nullable|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        try {
            // Update basic info
            $admin->name = $validated['name'];
            $admin->email = $validated['email'];
            
            // Update password if provided
            if (!empty($validated['new_password'])) {
                $admin->password = Hash::make($validated['new_password']);
            }
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $admin->clearMediaCollection('avatars');
                $admin->addMediaFromRequest('avatar')
                     ->usingFileName('avatar-' . time() . '.' . $request->file('avatar')->getClientOriginalExtension())
                     ->toMediaCollection('avatars');
            }
            
            $admin->save();
            
            return back()->with('success', 'Account settings updated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating account settings: ' . $e->getMessage());
        }
    }

    /**
     * Update the admin's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $admin = auth('admin')->user();
            $admin->update([
                'password' => Hash::make($request->password),
            ]);

            return back()->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating password: ' . $e->getMessage());
        }
    }

    /**
     * Calculate storage usage
     */
    protected function getStorageUsage()
    {
        $total = disk_total_space(storage_path());
        $free = disk_free_space(storage_path());
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    { 
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    /**
     * Get revenue trend data for the last 6 months
     */
    protected function getRevenueTrend()
    {
        $revenueData = [];
        $months = collect(range(5, 0))->map(function ($month) use (&$revenueData) {
            $date = now()->subMonths($month);
            $monthName = $date->format('M');
            
            $revenue = Membership::where('memberships.status', 'active')
                ->whereYear('memberships.created_at', $date->year)
                ->whereMonth('memberships.created_at', $date->month)
                ->join('plans', 'memberships.plan_id', '=', 'plans.id')
                ->sum('plans.price');
                
            $revenueData[] = [
                'month' => $monthName,
                'revenue' => $revenue
            ];
            
            return $monthName;
        });
        
        return [
            'labels' => $months->toArray(),
            'data' => array_column($revenueData, 'revenue')
        ];
    }

    /**
     * Get user growth data for the last 6 months
     */
    protected function getUserGrowth()
    {
        $growthData = [];
        $months = collect(range(5, 0))->map(function ($month) use (&$growthData) {
            $date = now()->subMonths($month);
            $monthName = $date->format('M');
            
            $count = User::whereYear('users.created_at', $date->year)
                ->whereMonth('users.created_at', '<=', $date->month)
                ->count();
                
            $growthData[] = $count;
            return $monthName;
        });
        
        return [
            'labels' => $months->toArray(),
            'data' => $growthData
        ];
    }

    /**
     * Display admin profile
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Get revenue data for the dashboard chart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRevenueData(Request $request)
    {
        $filter = $request->query('filter', 'monthly');
        $year = $request->query('year', date('Y'));
        
        if ($filter === 'yearly') {
            // Get data for last 5 years
            $currentYear = (int)date('Y');
            $startYear = $currentYear - 4; // Last 5 years including current
            
            $revenueData = [];
            $labels = [];
            $hasData = false;
            
            for ($year = $startYear; $year <= $currentYear; $year++) {
                $yearlyRevenue = \App\Models\Payment::where('status', 'succeeded')
                    ->where('payment_method', '!=', 'refund')
                    ->whereYear('created_at', $year)
                    ->sum('amount');
                
                $revenueData[] = (float)$yearlyRevenue;
                $labels[] = $year;
                
                if ($yearlyRevenue > 0) {
                    $hasData = true;
                }
            }
            
            // If no data found, generate dummy data
            if (!$hasData) {
                $revenueData = [];
                $labels = [];
                for ($i = 0; $i < 5; $i++) {
                    $labels[] = ($currentYear - 4 + $i);
                    $revenueData[] = rand(5000, 50000);
                }
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'amounts' => $revenueData,
                'is_dummy' => !$hasData
            ]);
            
        } else {
            // Default to monthly data for current year
            $monthlyData = [];
            $labels = [];
            $hasData = false;
            
            for ($month = 1; $month <= 12; $month++) {
                $monthlyRevenue = \App\Models\Payment::where('status', 'succeeded')
                    ->where('payment_method', '!=', 'refund')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->sum('amount');
                
                $monthlyData[] = (float)$monthlyRevenue;
                $labels[] = date('M', mktime(0, 0, 0, $month, 1));
                
                if ($monthlyRevenue > 0) {
                    $hasData = true;
                }
            }
            
            // If no data found, generate dummy data
            if (!$hasData) {
                $monthlyData = [];
                $labels = [];
                for ($month = 1; $month <= 12; $month++) {
                    $labels[] = date('M', mktime(0, 0, 0, $month, 1));
                    $monthlyData[] = rand(1000, 10000);
                }
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'amounts' => $monthlyData,
                'is_dummy' => !$hasData
            ]);
        }
    }

    /**
     * Display the specified gift.
     *
     * @param  \App\Models\Gift  $gift
     * @return \Illuminate\Http\Response
     */
    public function showGift(Gift $gift)
    {
        // Load gift with its relationships
        $gift->load([
            'category',
            'userGifts' => function ($query) {
                $query->with(['sender' => function($q) {
                        $q->with('media');
                    }, 'receiver' => function($q) {
                        $q->with('media');
                    }])
                    ->latest()
                    ->take(10); // Limit to 10 most recent gifts
            },
            'media'
        ]);

        // Calculate gift statistics
        $totalGiftsSent = $gift->userGifts()->count();
        $totalRevenue = $gift->userGifts()->sum('price');
        
        // Get top senders with proper grouping
        $topSenders = DB::table('user_gifts')
            ->select('users.id', 'users.name', 'users.email',
                    DB::raw('count(*) as gift_count'))
            ->join('users', 'user_gifts.sender_id', '=', 'users.id')
            ->where('user_gifts.gift_id', $gift->id)
            ->whereNull('users.deleted_at')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('gift_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $userModel = \App\Models\User::with('media')->find($user->id);
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $userModel->getFirstMediaUrl('profile_image') 
                        ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF',
                    'gift_count' => $user->gift_count
                ];
            });

        // Get top receivers with proper grouping
        $topReceivers = DB::table('user_gifts')
            ->select('users.id', 'users.name', 'users.email',
                    DB::raw('count(*) as received_count'))
            ->join('users', 'user_gifts.receiver_id', '=', 'users.id')
            ->where('user_gifts.gift_id', $gift->id)
            ->whereNull('users.deleted_at')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('received_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $userModel = \App\Models\User::with('media')->find($user->id);
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $userModel->getFirstMediaUrl('profile_image')
                        ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF',
                    'received_count' => $user->received_count
                ];
            });

        return view('admin.gifts.show', compact(
            'gift',
            'totalGiftsSent',
            'totalRevenue',
            'topSenders',
            'topReceivers'
        ));
    }
}
