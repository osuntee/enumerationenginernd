<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use Illuminate\Http\Request;

class AdminGatewaysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gateways = Gateway::all();
        return view('admin.gateways.index', compact('gateways'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.gateways.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'secret_key' => ['required', 'string', 'max:255'],
        ]);

        Gateway::create([
            'name' => $validated['name'],
            'secret_key' => $validated['secret_key'],
        ]);

        return redirect()->route('gateways.index')
            ->with('success', 'Gateway added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gateway $gateway)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gateway $gateway)
    {
        return view('admin.gateways.edit', compact('gateway'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gateway $gateway)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'secret_key' => ['required', 'string', 'max:255'],
        ]);

        $gateway->update([
            'name' => $validated['name'],
            'secret_key' => $validated['secret_key'],
        ]);

        return redirect()->back()->with('success', 'Gateway updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gateway $gateway)
    {
        $gateway->delete();
        return redirect()->route('gateways.index')
            ->with('success', 'Gateway deleted successfully.');
    }

    /**
     * Suspend the specified gateway.
     */
    public function suspend(Gateway $gateway)
    {
        $gateway->update(['is_active' => 0]);

        return redirect()->route('gateways.index')
            ->with('success', 'Payment gateway suspended successfully.');
    }

    /**
     * Activate the specified gateway.
     */
    public function activate(Gateway $gateway)
    {
        $gateway->update(['is_active' => 1]);

        return redirect()->route('gateways.index')
            ->with('success', 'Payment gateway activated successfully.');
    }
}
