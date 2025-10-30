<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user account is active
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been ' . $user->status . '. Please contact administrator.');
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Redirect user to their appropriate dashboard based on their role
            $redirectUrl = match($user->role) {
                'super_admin', 'admin' => '/admin/dashboard',
                'sales_manager' => '/manager/dashboard',
                'sales_rep' => '/sales/dashboard',
                'inventory_manager' => '/inventory/dashboard',
                'marketing' => '/marketing/dashboard',
                default => '/dashboard',
            };

            return redirect($redirectUrl)
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}