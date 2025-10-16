<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of the admins.
     */
    public function index()
    {
        $admins = User::where('role', '!=', 'user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        return view('admin.admins.create');
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:admin,super_admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Display the specified admin.
     */
    public function show(User $admin)
    {
        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(User $admin)
    {
        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, User $admin)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'role' => ['required', 'in:admin,super_admin'],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $admin->update($updateData);

        return redirect()->route('admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Suspend the specified admin.
     */
    public function suspend(User $admin)
    {

        // Prevent self-suspension
        if ($admin->id === Auth::id()) {
            return redirect()->route('admins.index')
                ->with('error', 'You cannot suspend yourself.');
        }

        $admin->update(['status' => 'suspended']);

        return redirect()->route('admins.index')
            ->with('success', 'Admin suspended successfully.');
    }

    /**
     * Activate the specified admin.
     */
    public function activate(User $admin)
    {
        $admin->update(['status' => 'active']);

        return redirect()->route('admins.index')
            ->with('success', 'Admin activated successfully.');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(User $admin)
    {
        // Prevent self-deletion
        if ($admin->id === Auth::id()) {
            return redirect()->route('admins.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $admin->delete();

        return redirect()->route('admins.index')
            ->with('success', 'Admin deleted successfully.');
    }
}
