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
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        $dates = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');
            $counts[] = $enumerationsLast7Days->get($date, 0);
        }

        $chartData = [
            'dates' => $dates,
            'counts' => $counts,
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
