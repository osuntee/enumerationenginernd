<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    /**
     * Display the specified staff member.
     */
    public function show(Project $project, Staff $staff)
    {
        $staff->load(['enumerations' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('admin.staff.show', compact('project', 'staff'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Staff $staff)
    {
        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('staff')->where(function ($query) use ($staff) {
                    return $query->where('customer_id', $staff->customer_id);
                })->ignore($staff->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'staff_type' => ['required', 'string', Rule::in(['admin', 'user'])],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'staff_type' => $request->staff_type,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        $staff->update($updateData);

        return redirect()->route('customers.staff.show', $staff)
            ->with('success', 'Staff member updated successfully!');
    }

    /**
     * Show the form for changing password.
     */
    public function showChangePassword(Project $project, Staff $staff)
    {
        return view('admin.staff.change-password', compact('project', 'staff'));
    }

    /**
     * Update the staff member's password.
     */
    public function updatePassword(Request $request, Project $project, Staff $staff)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $staff->update([
            'password' => $request->password
        ]);

        return redirect()->route('staff.show', [$project, $staff])
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Toggle the active status of a staff member.
     */
    public function toggleStatus(Project $project, Staff $staff)
    {
        $staff->update(['is_active' => !$staff->is_active]);

        $status = $staff->is_active ? 'activated' : 'suspended';

        return redirect()->back()
            ->with('success', "Staff member {$status} successfully!");
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Project $project, Staff $staff)
    {
        // Check if staff has any enumerations
        $enumerationCount = $staff->enumerations()->count();

        if ($enumerationCount > 0) {
            return redirect()->route('staff.index', $project)
                ->with('error', 'Cannot delete staff member with existing enumerations. Suspend instead.');
        }

        $staffName = $staff->name;
        $staff->delete();

        return redirect()->route('staff.index', $project)
            ->with('success', "Staff member '{$staffName}' deleted successfully!");
    }
}
