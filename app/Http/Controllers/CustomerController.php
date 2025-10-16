<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Staff;
use App\Models\Project;
use App\Models\ProjectField;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort functionality
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'email', 'phone', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $customers = $query->withCount(['projects', 'staff'])
            ->orderBy($sortBy, $sortDirection)
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $customer = new Customer();
        return view('admin.customers.create', compact('customer'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Customer validation
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],

            // Staff validation
            'staff_name' => ['required', 'string', 'max:255'],
            'staff_email' => ['required', 'email', 'max:255', 'unique:staff,email'],
            'staff_phone' => ['nullable', 'string', 'max:20'],
            'staff_password' => ['required', 'string', 'min:8', 'confirmed'],
            'staff_is_active' => ['boolean']
        ]);

        try {
            DB::beginTransaction();

            // Set default value for customer is_active if not provided
            $validated['is_active'] = $validated['is_active'] ?? true;

            // Create customer
            $customer = Customer::create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'is_active' => $validated['is_active']
            ]);

            // Create super admin staff for this customer
            Staff::create([
                'customer_id' => $customer->id,
                'name' => $validated['staff_name'],
                'email' => $validated['staff_email'],
                'phone' => $validated['staff_phone'],
                'password' => $validated['staff_password'],
                'staff_type' => Staff::STAFF_TYPE_SUPER_ADMIN,
                'is_active' => $validated['staff_is_active'] ?? true
            ]);

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', "Customer '{$customer->name}' and super admin staff member '{$validated['staff_name']}' have been created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the customer and staff member. Please try again.');
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $customer->load([
            'projects' => function ($query) {
                $query->withCount('staff')->latest();
            },
            'staff' => function ($query) {
                $query->withCount('projects')->latest();
            }
        ]);

        // Get statistics
        $stats = [
            'total_projects' => $customer->projects->count(),
            'active_projects' => $customer->projects->where('is_active', true)->count(),
            'total_staff' => $customer->staff->count(),
            'active_staff' => $customer->staff->where('is_active', true)->count(),
        ];

        return view('admin.customers.show', compact('customer', 'stats'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean']
        ]);

        // Handle checkbox value (if not checked, it won't be in the request)
        $validated['is_active'] = $request->has('is_active');

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' has been updated successfully.");
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            // Check if customer has any projects or staff
            $projectsCount = $customer->projects()->count();
            $staffCount = $customer->staff()->count();

            if ($projectsCount > 0 || $staffCount > 0) {
                return redirect()->route('customers.index')
                    ->with('error', "Cannot delete customer '{$customer->name}' because they have {$projectsCount} projects and {$staffCount} staff members. Please remove all associated data first or deactivate the customer instead.");
            }

            $customerName = $customer->name;
            $customer->delete();

            return redirect()->route('customers.index')
                ->with('success', "Customer '{$customerName}' has been deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', 'An error occurred while deleting the customer. Please try again.');
        }
    }

    /**
     * Toggle the active status of a customer.
     */
    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $newStatus = !$customer->is_active;
        $statusText = $newStatus ? 'activated' : 'deactivated';

        $customer->update(['is_active' => $newStatus]);

        return redirect()->back()
            ->with('success', "Customer '{$customer->name}' has been {$statusText} successfully.");
    }

    /**
     * Get customers for AJAX requests (e.g., for dropdowns).
     */
    public function ajax(Request $request)
    {
        $query = Customer::active();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->select('id', 'name', 'email')
            ->limit(20)
            ->get();

        return response()->json($customers);
    }

    /**
     * Export customers data.
     */
    public function export(Request $request)
    {
        $query = Customer::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $customers = $query->withCount(['projects', 'staff'])->get();

        $filename = 'customers_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Address',
                'Status',
                'Projects Count',
                'Staff Count',
                'Created At',
                'Updated At'
            ]);

            // Add data rows
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->address,
                    $customer->is_active ? 'Active' : 'Inactive',
                    $customer->projects_count,
                    $customer->staff_count,
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk actions for customers.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['exists:customers,id']
        ]);

        $customerIds = $validated['customer_ids'];
        $action = $validated['action'];

        try {
            switch ($action) {
                case 'activate':
                    Customer::whereIn('id', $customerIds)->update(['is_active' => true]);
                    $message = count($customerIds) . ' customers have been activated successfully.';
                    break;

                case 'deactivate':
                    Customer::whereIn('id', $customerIds)->update(['is_active' => false]);
                    $message = count($customerIds) . ' customers have been deactivated successfully.';
                    break;

                case 'delete':
                    // Check if any customers have projects or staff
                    $customersWithData = Customer::whereIn('id', $customerIds)
                        ->whereHas('projects')
                        ->orWhereHas('staff')
                        ->count();

                    if ($customersWithData > 0) {
                        return redirect()->back()
                            ->with('error', 'Some customers cannot be deleted because they have associated projects or staff. Please remove all associated data first.');
                    }

                    Customer::whereIn('id', $customerIds)->delete();
                    $message = count($customerIds) . ' customers have been deleted successfully.';
                    break;
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while performing the bulk action. Please try again.');
        }
    }

    /**
     * Display projects associated with the customer.
     */
    public function projects(Customer $customer): View
    {
        $projects = $customer->projects()->withCount('staff')->latest()->paginate(15);
        return view('admin.customers.projects.index', compact('customer', 'projects'));
    }

    /**
     * Show the form for creating a new project for the customer.
     */
    public function createProject(Customer $customer): View
    {
        return view('admin.customers.projects.create', compact('customer'));
    }

    /**
     * Store a newly created project for the customer.
     */
    public function storeProject(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                'max:255'
            ],
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => [
                'required',
                'string',
                Rule::in(['text', 'textarea', 'number', 'email', 'url', 'tel', 'date', 'time', 'datetime-local', 'select', 'radio', 'checkbox', 'checkboxes', 'file', 'color', 'range'])
            ],
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|string',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.help_text' => 'nullable|string|max:500',
            'fields.*.default_value' => 'nullable|string',
            'fields.*.validation_rules' => 'nullable|string',
            'fields.*.min' => 'nullable|numeric',
            'fields.*.max' => 'nullable|numeric',
            'fields.*.step' => 'nullable|numeric',
            'fields.*.accept' => 'nullable|string',
            'fields.*.maxlength' => 'nullable|integer',
            'fields.*.max_size' => 'nullable|integer',
        ]);

        // Validate field names are unique within the project
        $fieldNames = collect($request->fields)->pluck('name');
        if ($fieldNames->count() !== $fieldNames->unique()->count()) {
            return back()->withErrors(['fields' => 'Field names must be unique within a project.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Create the project
            $project = Project::create([
                'customer_id' => $customer->id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // Create project fields
            foreach ($request->fields as $index => $fieldData) {
                $this->createProjectField($project, $fieldData, $index);
            }

            DB::commit();

            return redirect()->route('customers.projects.index', $customer)
                ->with('success', "Project '{$project->name}' has been created successfully for customer '{$customer->name}' with " . count($request->fields) . " fields.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to create project: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Helper method to create a project field
     */
    private function createProjectField(Project $project, array $fieldData, int $order)
    {
        $attributes = [];

        // Build attributes array based on field type
        switch ($fieldData['type']) {
            case 'number':
            case 'range':
                if (!empty($fieldData['min'])) $attributes['min'] = (float) $fieldData['min'];
                if (!empty($fieldData['max'])) $attributes['max'] = (float) $fieldData['max'];
                if (!empty($fieldData['step'])) $attributes['step'] = (float) $fieldData['step'];
                break;

            case 'text':
            case 'textarea':
                if (!empty($fieldData['maxlength'])) $attributes['maxlength'] = (int) $fieldData['maxlength'];
                break;

            case 'file':
                if (!empty($fieldData['accept'])) $attributes['accept'] = $fieldData['accept'];
                if (!empty($fieldData['max_size'])) $attributes['max_size'] = (int) $fieldData['max_size'];
                break;
        }

        $options = null;
        if (in_array($fieldData['type'], ['select', 'radio', 'checkboxes']) && !empty($fieldData['options'])) {
            $options = array_filter(array_map('trim', explode(',', $fieldData['options'])));
        }

        return ProjectField::create([
            'project_id' => $project->id,
            'name' => $fieldData['name'],
            'label' => $fieldData['label'],
            'type' => $fieldData['type'],
            'required' => $fieldData['required'] ?? false,
            'placeholder' => $fieldData['placeholder'] ?? null,
            'help_text' => $fieldData['help_text'] ?? null,
            'default_value' => $fieldData['default_value'] ?? null,
            'validation_rules' => $fieldData['validation_rules'] ?? null,
            'options' => $options,
            'attributes' => !empty($attributes) ? $attributes : null,
            'order' => $order,
        ]);
    }

    /**
     * Display staff associated with the customer.
     */
    public function staff(Customer $customer): View
    {
        $staff = $customer->staff()->withCount('projects')->latest()->paginate(15);
        return view('admin.customers.staff.index', compact('customer', 'staff'));
    }

    /**
     * Show the form for creating a new staff member for the customer.
     */
    public function createStaff(Customer $customer): View
    {
        return view('admin.customers.staff.create', compact('customer'));
    }

    /**
     * Store a newly created staff member for the customer.
     */
    public function storeStaff(Request $request, Customer $customer): RedirectResponse
    {
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

        return redirect()->route('customers.staff.index', $customer)
            ->with('success', 'Staff member added successfully!');
    }
}
