<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Enumeration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalProjects = Project::count();
        $totalStaff = Staff::count();
        $totalEnumerations = Enumeration::count();

        // Projects per customer
        $projectsPerCustomer = Customer::withCount('projects')
            ->orderBy('projects_count', 'desc')
            ->limit(5)
            ->get();

        // Enumerations over the last 7 days
        $enumerationsLast7Days = Enumeration::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [
            'dates' => $enumerationsLast7Days->pluck('date'),
            'counts' => $enumerationsLast7Days->pluck('count'),
        ];

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalProjects',
            'totalStaff',
            'totalEnumerations',
            'projectsPerCustomer',
            'chartData'
        ));
    }
}
