<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\AnalyticsController;

// Authentication routes
require __DIR__ . '/auth.php';

// Welcome page (public)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard routes - role-based redirects
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes
    Route::middleware(['role:super_admin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::get('/analytics', [AnalyticsController::class, 'admin'])->name('analytics');
    });

    // Sales Manager routes
    Route::middleware(['role:sales_manager'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');
        Route::get('/team-performance', [AnalyticsController::class, 'teamPerformance'])->name('team-performance');
        Route::get('/team-members', [UserController::class, 'teamMembers'])->name('team-members');
    });

    // Sales Representative routes
    Route::middleware(['role:sales_rep'])->prefix('sales')->name('sales.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'sales'])->name('dashboard');
        Route::resource('leads', LeadController::class);
        Route::resource('companies', CompanyController::class)->only(['index', 'show']);
        Route::resource('contacts', ContactController::class)->only(['index', 'show']);
        Route::resource('sales-orders', SalesOrderController::class)->only(['index', 'show', 'create', 'store']);
        Route::get('/my-performance', [AnalyticsController::class, 'myPerformance'])->name('performance');
    });

    // Inventory Manager routes
    Route::middleware(['role:inventory_manager'])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'inventory'])->name('dashboard');
        Route::resource('products', ProductController::class);
        Route::get('/stock-levels', [ProductController::class, 'stockLevels'])->name('stock-levels');
        Route::get('/inventory-reports', [AnalyticsController::class, 'inventory'])->name('reports');
    });

    // Marketing routes
    Route::middleware(['role:marketing'])->prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'marketing'])->name('dashboard');
        Route::resource('leads', LeadController::class)->only(['index', 'show']);
        Route::get('/campaigns', function () { return view('marketing.campaigns'); })->name('campaigns');
        Route::get('/customer-analytics', [AnalyticsController::class, 'customerAnalytics'])->name('customer-analytics');
    });

    // Shared routes for authenticated users
    Route::resource('companies', CompanyController::class)->except(['create']);
    Route::resource('contacts', ContactController::class)->except(['create']);
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');

    // Search routes
    Route::get('/search/companies', [CompanyController::class, 'search'])->name('search.companies');
    Route::get('/search/contacts', [ContactController::class, 'search'])->name('search.contacts');
    Route::get('/search/products', [ProductController::class, 'search'])->name('search.products');
});