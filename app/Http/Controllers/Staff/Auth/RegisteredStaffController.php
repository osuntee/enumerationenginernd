<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredStaffController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('staff.auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . Staff::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'staff_type' => ['required', 'in:admin,user'], // Super admin can only be created manually
        ]);

        $staff = Staff::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'customer_id' => $request->customer_id,
            'staff_type' => $request->staff_type ?? 'user',
            'is_active' => true, // You might want to set this to false and require admin approval
        ]);

        event(new Registered($staff));

        Auth::guard('staff')->login($staff);

        return redirect()->route('staff.dashboard');
    }
}
