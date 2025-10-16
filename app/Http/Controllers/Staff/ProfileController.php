<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the staff's profile form.
     */
    public function edit(Request $request): View
    {
        return view('staff.profile.edit', [
            'staff' => $request->user('staff'),
        ]);
    }

    /**
     * Update the staff's profile information.
     */
    public function update(StaffProfileUpdateRequest $request): RedirectResponse
    {
        $staff = $request->user('staff');
        $staff->fill($request->validated());

        if ($staff->isDirty('email')) {
            $staff->email_verified_at = null;
        }

        $staff->save();

        return Redirect::route('staff.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the staff's account.
     * Note: This might be restricted based on staff type or require admin approval
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('staffDeletion', [
            'password' => ['required', 'current_password:staff'],
        ]);

        $staff = $request->user('staff');

        // Prevent super admin from deleting their own account if they're the only one
        if ($staff->isSuperAdmin()) {
            $superAdminCount = \App\Models\Staff::where('staff_type', 'super_admin')
                ->where('is_active', true)
                ->count();

            if ($superAdminCount <= 1) {
                return back()->withErrors([
                    'password' => 'Cannot delete the last active super admin account.',
                ]);
            }
        }

        // Log out the staff member
        Auth::guard('staff')->logout();

        // Instead of deleting, you might want to deactivate the account
        // $staff->update(['is_active' => false]);

        // Or actually delete the account
        $staff->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/staff/login');
    }

    /**
     * Show the staff's profile view (read-only).
     */
    public function show(Request $request): View
    {
        return view('staff.profile.show', [
            'staff' => $request->user('staff'),
        ]);
    }
}
