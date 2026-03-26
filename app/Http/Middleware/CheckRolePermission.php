<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role = null, $permission = null): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Check for role
        if ($role && !$user->hasRole($role)) {
            abort(403, 'Unauthorized action.');
        }

        // Check for permission
        if ($permission && !$user->can($permission)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
