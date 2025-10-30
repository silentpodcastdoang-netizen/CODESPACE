<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard based on user role.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Redirect to appropriate dashboard based on role
        return match($user->role) {
            'super_admin', 'admin' => $this->admin($request),
            'sales_manager' => $this->manager($request),
            'sales_rep' => $this->sales($request),
            'inventory_manager' => $this->inventory($request),
            'marketing' => $this->marketing($request),
            default => Inertia::render('Dashboard'),
        };
    }

    /**
     * Display the admin dashboard.
     */
    public function admin(Request $request): Response
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_companies' => Company::count(),
            'total_contacts' => Contact::count(),
            'total_leads' => Lead::count(),
            'total_orders' => SalesOrder::count(),
            'revenue_this_month' => SalesOrder::whereMonth('created_at', now()->month)
                ->where('status', 'delivered')
                ->sum('total_amount'),
            'new_leads_this_month' => Lead::whereMonth('created_at', now()->month)->count(),
        ];

        $recentActivities = $this->getRecentActivities();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Display the sales manager dashboard.
     */
    public function manager(Request $request): Response
    {
        $manager = Auth::user();
        $teamMembers = $manager->teamMembers;

        $teamStats = [
            'total_team_members' => $teamMembers->count(),
            'active_team_members' => $teamMembers->filter(fn($member) => $member->user->isActive())->count(),
            'team_leads' => Lead::whereIn('assigned_to', $teamMembers->pluck('user_id'))->count(),
            'team_orders' => SalesOrder::whereIn('sales_rep_id', $teamMembers->pluck('user_id'))->count(),
            'team_revenue' => SalesOrder::whereIn('sales_rep_id', $teamMembers->pluck('user_id'))
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
        ];

        $teamPerformance = $this->getTeamPerformance($teamMembers->pluck('user_id'));

        return Inertia::render('Manager/Dashboard', [
            'teamStats' => $teamStats,
            'teamPerformance' => $teamPerformance,
        ]);
    }

    /**
     * Display the sales representative dashboard.
     */
    public function sales(Request $request): Response
    {
        $salesRep = Auth::user();

        $stats = [
            'total_companies' => $salesRep->assignedCompanies()->count(),
            'total_contacts' => Contact::whereHas('company', function ($query) use ($salesRep) {
                $query->where('assigned_to', $salesRep->id);
            })->count(),
            'my_leads' => $salesRep->assignedLeads()->count(),
            'my_orders' => $salesRep->salesOrders()->count(),
            'revenue_this_month' => $salesRep->salesOrders()
                ->whereMonth('created_at', now()->month)
                ->where('status', 'delivered')
                ->sum('total_amount'),
            'conversion_rate' => $this->getConversionRate($salesRep),
        ];

        $recentLeads = $salesRep->assignedLeads()
            ->with(['company', 'contact'])
            ->latest()
            ->take(5)
            ->get();

        $recentOrders = $salesRep->salesOrders()
            ->with(['company', 'items.product'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Sales/Dashboard', [
            'stats' => $stats,
            'recentLeads' => $recentLeads,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Display the inventory manager dashboard.
     */
    public function inventory(Request $request): Response
    {
        // This would require Product and StockLevel models
        // For now, returning placeholder data
        $stats = [
            'total_products' => 0, // Product::count(),
            'low_stock_products' => 0, // Product::where('stock_level', '<=', 'reorder_level')->count(),
            'total_warehouses' => 0, // Warehouse::count(),
            'out_of_stock' => 0, // StockLevel::where('quantity', 0)->count(),
        ];

        return Inertia::render('Inventory/Dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * Display the marketing dashboard.
     */
    public function marketing(Request $request): Response
    {
        $stats = [
            'total_leads' => Lead::count(),
            'new_leads_this_month' => Lead::whereMonth('created_at', now()->month)->count(),
            'lead_sources' => Lead::selectRaw('source, count(*) as count')
                ->groupBy('source')
                ->get(),
            'conversion_rate' => $this->getOverallConversionRate(),
        ];

        $leadsByStatus = Lead::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        return Inertia::render('Marketing/Dashboard', [
            'stats' => $stats,
            'leadsByStatus' => $leadsByStatus,
        ]);
    }

    /**
     * Get recent activities for admin dashboard.
     */
    private function getRecentActivities()
    {
        // This would query activity logs or recent changes
        // For now, returning empty array
        return [];
    }

    /**
     * Get team performance data.
     */
    private function getTeamPerformance($teamMemberIds)
    {
        // This would calculate performance metrics for team members
        // For now, returning empty collection
        return collect([]);
    }

    /**
     * Get conversion rate for a sales representative.
     */
    private function getConversionRate($salesRep): float
    {
        $totalLeads = $salesRep->assignedLeads()->count();
        $convertedLeads = $salesRep->assignedLeads()
            ->whereIn('status', ['closed_won'])
            ->count();

        if ($totalLeads === 0) {
            return 0;
        }

        return round(($convertedLeads / $totalLeads) * 100, 2);
    }

    /**
     * Get overall conversion rate.
     */
    private function getOverallConversionRate(): float
    {
        $totalLeads = Lead::count();
        $convertedLeads = Lead::whereIn('status', ['closed_won'])->count();

        if ($totalLeads === 0) {
            return 0;
        }

        return round(($convertedLeads / $totalLeads) * 100, 2);
    }
}