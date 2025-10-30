<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:sales_rep,sales_manager,inventory_manager,marketing',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'employee_id' => 'nullable|string|max:50|unique:user_profiles',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // Create user profile
        $user->profile()->create([
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'commission_rate' => $this->getDefaultCommissionRate($request->role),
            'target_sales' => $this->getDefaultTargetSales($request->role),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Update last login timestamp
        $user->updateLastLogin();

        // Redirect based on user role
        $redirectUrl = match($user->role) {
            'sales_manager' => '/manager/dashboard',
            'sales_rep' => '/sales/dashboard',
            'inventory_manager' => '/inventory/dashboard',
            'marketing' => '/marketing/dashboard',
            default => '/dashboard',
        };

        return redirect($redirectUrl);
    }

    /**
     * Get default commission rate based on role.
     */
    private function getDefaultCommissionRate(string $role): float
    {
        return match($role) {
            'sales_manager' => 5.00,
            'sales_rep' => 3.00,
            default => 0.00,
        };
    }

    /**
     * Get default target sales based on role.
     */
    private function getDefaultTargetSales(string $role): float
    {
        return match($role) {
            'sales_manager' => 100000.00,
            'sales_rep' => 50000.00,
            default => 0.00,
        };
    }
}