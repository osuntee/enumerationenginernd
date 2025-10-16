<?php

namespace App\Http\Controllers\Staff\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $projects = $user->projects()->get();
        $notifications = $user->notifications()->get();
        $activities = $user->activities()->get();

        return response()->json([
            'status' => 'Request successful',
            'user' => $user,
            'projects' => $projects,
            'notifications' => $notifications,
            'activities' => $activities,
        ], 200);
    }
}
