<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StaffLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('staff.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(StaffLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $staff = Auth::guard('staff')->user();

        // Chech staff role
        if ($staff->staff_type  !== 'super_admin' && $staff->staff_type  !== 'admin') {
            Auth::guard('staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Unauthorized access. Please contact administrator.',
            ]);
        }

        // Check if staff is active
        if (!$staff->is_active) {
            Auth::guard('staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact administrator.',
            ]);
        }

        return redirect()->intended(route('staff.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
