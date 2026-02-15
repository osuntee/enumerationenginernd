<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Enumeration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('staff')->user();
        $customer = $user->customer;

        $totalProjects = $customer->projects()->count();
        $totalStaff = $customer->staff()->count();

        // Count enumerations across all customer projects
        $totalEnumerations = Enumeration::whereIn('project_id', $customer->projects->pluck('id'))->count();

        $activeProjectsCount = $customer->projects()->where('is_active', true)->count();

        // Top projects by enumeration count
        $topProjects = Project::where('customer_id', $customer->id)
            ->withCount('enumerations')
            ->orderBy('enumerations_count', 'desc')
            ->limit(5)
            ->get();

        // Enumerations over the last 7 days for this customer
        $enumerationsLast7Days = Enumeration::whereIn('project_id', $customer->projects->pluck('id'))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
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

        return view('staff.dashboard', compact(
            'customer',
            'totalProjects',
            'totalStaff',
            'totalEnumerations',
            'activeProjectsCount',
            'topProjects',
            'chartData'
        ));
    }
}
