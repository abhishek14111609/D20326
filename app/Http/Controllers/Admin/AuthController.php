<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $credentials = $request->only('email', 'password');
        
        // Try to authenticate using admin guard
        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $admin = Auth::guard('admin')->user();
            
            // Check if admin is active
            if (!$admin->isActive()) {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Your account is not active. Please contact administrator.'
                ]);
            }
            
            // Update last login info
            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);
            
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.'
        ]);
    }

    /**
     * Show admin registration form
     */
    public function showRegisterForm()
    {
        return view('admin.auth.register');
    }

    /**
     * Handle admin registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['admin', 'moderator', 'super_admin'])],
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if current user can create this role
        if ($request->role === 'super_admin') {
            if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()->hasRole('super_admin')) {
                return back()->withErrors([
                    'role' => 'You do not have permission to create super admin accounts.'
                ]);
            }
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => 'active'
        ]);

        return redirect()->route('admin.login')->with('success', 'Admin account created successfully. You can now login.');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')->with('success', 'You have been successfully logged out.');
    }

    /**
     * Create default admin user (for setup)
     */
    public function createDefaultAdmin()
    {
        $adminEmail = 'admin@admin.duos.com';
        
        if (User::where('email', $adminEmail)->exists()) {
            return response()->json(['message' => 'Admin user already exists']);
        }

        $admin = Admin::create([
            'name' => 'DUOS Admin',
            'email' => $adminEmail,
            'password' => Hash::make('admin123'),
            'gender' => 'other',
            'dob' => '1990-01-01',
            'login_type' => 'email',
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Default admin created successfully',
            'email' => $adminEmail,
            'password' => 'admin123'
        ]);
    }
}
