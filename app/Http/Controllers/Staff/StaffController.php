<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::guard('staff')->user();

        $customer = $user->customer;
        $staff = $customer->staff()->withCount('projects')->latest()->paginate(15);

        return view('staff.staff.index', compact('customer', 'staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::guard('staff')->user();

        $customer = $user->customer;
        return view('staff.staff.create', compact('customer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('staff')->user();
        $customer = $user->customer;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('staff')->where(function ($query) use ($customer) {
                    return $query->where('customer_id', $customer->id);
                })
            ],
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'staff_type' => ['required', 'string', Rule::in(['admin', 'user'])],
        ]);

        Staff::create([
            'customer_id' => $customer->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'staff_type' => $request->staff_type,
            'is_active' => true,
        ]);

        return redirect()->route('staff.staff.index')
            ->with('success', 'Staff member added successfully!');
    }

    /**
     * Display the specified staff member.
     */
    public function show(Project $project, Staff $staff)
    {
        $staff->load(['enumerations' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('staff.staff.show', compact('project', 'staff'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Staff $staff)
    {
        return view('staff.staff.edit', compact('staff'));
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

        return redirect()->route('staff.staff.show', $staff)
            ->with('success', 'Staff member updated successfully!');
    }

    /**
     * Show the form for changing password.
     */
    public function showChangePassword(Project $project, Staff $staff)
    {
        return view('staff.staff.change-password', compact('project', 'staff'));
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
