<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('staff')->check()) {
            return redirect()->route('staff.login');
        }

        $staff = Auth::guard('staff')->user();

        // if ($staff->staff_type != 'super_admin' || $staff->staff_type != 'admin') {
        //     abort(403, 'Unauthorized access.');
        // }

        return $next($request);
    }
}
