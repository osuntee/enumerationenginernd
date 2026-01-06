<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Staff;
use App\Models\ProjectField;
use App\Models\ProjectPayment;
use App\Models\EnumerationPayment;
use App\Models\PaymentTransaction;
use App\Helpers\PaymentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['enumerations', 'projectFields', 'staff'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        return view('admin.projects.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pre_generate' => 'nullable|boolean',
            'allow_api' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'requires_verification' => 'nullable|boolean',
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
                'customer_id' => $request->customer_id,
                'name' => $request->name,
                'description' => $request->description,
                'allow_api' => $request->has('allow_api') ? (bool)$request->allow_api : false,
                'is_published' => $request->has('is_published') ? (bool)$request->is_published : false,
                'requires_verification' => $request->has('requires_verification') ? (bool)$request->requires_verification : false,
            ]);

            if ($project->is_published) {
                do {
                    $letters = 'abcdefghijklmnopqrstuvwxyz';
                    $code = collect(range(1, 15))
                        ->map(fn() => $letters[random_int(0, strlen($letters) - 1)])
                        ->implode('');
                } while (Project::where('code', $code)->exists());

                $project->code = $code;
                $project->save();
            }

            // Create project fields
            foreach ($request->fields as $index => $fieldData) {
                $this->createProjectField($project, $fieldData, $index);
            }

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project created successfully with ' . count($request->fields) . ' fields!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to create project: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Project $project)
    {
        $project->load([
            'projectFields' => function ($query) {
                $query->active()->ordered();
            },
            'staff' => function ($query) {
                $query->orderBy('is_active', 'desc')->orderBy('name');
            }
        ]);

        $enumerations = $project->enumerations()
            ->with(['enumerationData.projectField'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.projects.show', compact('project', 'enumerations'));
    }

    public function edit(Project $project)
    {
        $project->load(['projectFields' => function ($query) {
            $query->ordered();
        }]);

        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'allow_api' => 'nullable|boolean',
            'requires_verification' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
        ]);

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'allow_api' => $request->boolean('allow_api'),
            'requires_verification' => $request->boolean('requires_verification'),
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($project->is_published && !$project->code) {
            do {
                $letters = 'abcdefghijklmnopqrstuvwxyz';
                $code = collect(range(1, 15))
                    ->map(fn() => $letters[random_int(0, strlen($letters) - 1)])
                    ->implode('');
            } while (Project::where('code', $code)->exists());

            $project->code = $code;
            $project->save();
        }

        return redirect()->back()->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        // Check if project has any enumerations
        $enumerationCount = $project->enumerations()->count();

        if ($enumerationCount > 0) {
            return redirect()->route('projects.index')
                ->with('error', 'Cannot delete project with existing enumeration data. Deactivate instead.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Deactivate a project
     */
    public function deactivate(Project $project)
    {
        $project->update(['is_active' => false]);

        return redirect()->route('projects.index')
            ->with('success', 'Project deactivated successfully!');
    }

    /**
     * Activate a project
     */
    public function activate(Project $project)
    {
        $project->update(['is_active' => true]);

        return redirect()->route('projects.index')
            ->with('success', 'Project activated successfully!');
    }

    /**
     * Add a new field to an existing project
     */
    public function addField(Request $request, Project $project)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                'max:255',
                Rule::unique('project_fields')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                })
            ],
            'label' => 'required|string|max:255',
            'type' => [
                'required',
                'string',
                Rule::in(['text', 'textarea', 'number', 'email', 'url', 'tel', 'date', 'time', 'datetime-local', 'select', 'radio', 'checkbox', 'checkboxes', 'file', 'color', 'range'])
            ],
            'required' => 'boolean',
            'options' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'min' => 'nullable|numeric',
            'max' => 'nullable|numeric',
            'step' => 'nullable|numeric',
            'accept' => 'nullable|string',
            'maxlength' => 'nullable|integer',
            'max_size' => 'nullable|integer',
        ]);

        $maxOrder = $project->projectFields()->max('order') ?? -1;

        $this->createProjectField($project, $request->all(), $maxOrder + 1);

        return redirect()->route('projects.edit', $project)
            ->with('success', 'Field added successfully!');
    }

    /**
     * Update field order
     */
    public function updateFieldOrder(Request $request, Project $project)
    {
        $request->validate([
            'field_orders' => 'required|array',
            'field_orders.*' => 'required|integer|exists:project_fields,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->field_orders as $order => $fieldId) {
                ProjectField::where('id', $fieldId)
                    ->where('project_id', $project->id)
                    ->update(['order' => $order]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle field active status
     */
    public function toggleField(ProjectField $field)
    {
        $field->update(['is_active' => !$field->is_active]);

        $status = $field->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Field '{$field->label}' {$status} successfully!");
    }

    /**
     * Delete a field (only if no data exists)
     */
    public function deleteField(ProjectField $field)
    {
        // Check if field has any enumeration data
        $dataCount = $field->enumerationData()->count();

        if ($dataCount > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete field with existing data. Deactivate instead.');
        }

        $fieldLabel = $field->label;
        $field->delete();

        return redirect()->back()
            ->with('success', "Field '{$fieldLabel}' deleted successfully!");
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

    /*
    * Display staff associated with a project
    */
    public function staff(Project $project)
    {
        $staff = $project->staff()
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $allStaff = $project->customer->staff()
            ->where('customer_id', $project->customer_id)
            ->whereNotIn('id', $project->staff->pluck('id')) // exclude already assigned staff
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('admin.projects.staff.index', compact('project', 'staff', 'allStaff'));
    }

    /*
    * Display enumerations associated with a project
    */
    public function enumerations(Project $project)
    {
        $enumerations = $project->enumerations()
            ->with(['enumerationData.projectField'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.projects.enumerations', compact('project', 'enumerations'));
    }

    /*
    * Assign staff to a project
    */
    public function assign(Request $request, Project $project)
    {
        $request->validate([
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(function ($query) use ($project) {
                    $query->where('customer_id', $project->customer_id);
                })
            ],
        ]);

        $project->staff()->syncWithoutDetaching($request->staff_ids);

        return redirect()->route('projects.staff.index', $project)
            ->with('success', 'Staff assigned to project successfully!');
    }

    /*
    * Remove staff from a project
    */
    public function remove(Project $project, Staff $staff)
    {
        $project->staff()->detach($staff->id);

        return redirect()
            ->back()
            ->with('success', "{$staff->name} has been removed from the project.");
    }

    /*
    * Display payments associated with a project
    */
    public function payments(Project $project)
    {
        $payments = $project->projectPayments()->orderBy('created_at', 'desc')->get();

        return view('admin.projects.payments.index', compact('project', 'payments'));
    }

    /*
    * Create payment form
    */
    public function createPayment(Project $project)
    {
        return view('admin.projects.payments.create', compact('project'));
    }

    /**
     * Store a newly created project payment.
     */
    public function storePayment(Project $project, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:one_off,weekly,monthly,yearly',
            'description' => 'nullable|string|max:1000',
            'allow_partial_payments' => 'boolean',
            'payment_type' => 'required|in:manual,gateway,both',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $validated['project_id'] = $project->id;
        $validated['is_active'] = true;
        $validated['allow_partial_payments'] = $request->boolean('allow_partial_payments');

        $projectPayment = ProjectPayment::create($validated);

        // Generate enumeration payments for existing enumerations if this is a one-off payment
        if ($validated['frequency'] === 'one_off') {
            PaymentHelper::generateNewEnumerationPayments($projectPayment);
        }

        return redirect()->route('projects.payments.index', $project)
            ->with('success', 'Project payment created successfully!');
    }

    /**
     * Display the specified project payment.
     */
    public function showPayment(ProjectPayment $payment)
    {
        $payment->load(['project', 'enumerationPayments.enumeration', 'enumerationPayments.paymentTransactions']);

        // Get payment statistics
        $stats = [
            'total_enumerations' => $payment->enumerationPayments()->count(),
            'total_collected' => $payment->getTotalCollected(),
            'total_outstanding' => $payment->getTotalOutstanding(),
            'pending_count' => $payment->enumerationPayments()->pending()->count(),
            'overdue_count' => $payment->enumerationPayments()->overdue()->count(),
            'paid_count' => $payment->enumerationPayments()->paid()->count(),
        ];

        return view('admin.projects.payments.show', compact('payment', 'stats'));
    }

    /**
     * Show the form for editing the specified project payment.
     */
    public function editPayment(ProjectPayment $payment)
    {
        $project = $payment->project;
        return view('admin.projects.payments.edit', compact('project', 'payment'));
    }

    /**
     * Update the specified project payment.
     */
    public function updatePayment(Request $request, ProjectPayment $payment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:one_off,weekly,monthly,yearly',
            'description' => 'nullable|string|max:1000',
            'allow_partial_payments' => 'boolean',
            'payment_type' => 'required|in:manual,gateway,both',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $validated['allow_partial_payments'] = $request->boolean('allow_partial_payments');

        $payment->update($validated);

        return redirect()->route('projects.payments.edit', $payment)
            ->with('success', 'Project payment updated successfully!');
    }

    /**
     * Display enumeration payments for a specific project payment
     */
    public function enumerationPayments(ProjectPayment $payment)
    {
        // Build the base query
        $query = $payment->enumerationPayments()
            ->with(['enumeration', 'paymentTransactions']);

        // Apply search filter
        if (request('search')) {
            $search = request('search');
            $query->whereHas('enumeration', function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if (request('status')) {
            $status = request('status');
            switch ($status) {
                case 'paid':
                    $query->whereRaw('amount_paid >= amount_due');
                    break;
                case 'pending':
                    $query->whereRaw('amount_paid < amount_due')
                        ->where(function ($q) {
                            $q->whereNull('due_date')
                                ->orWhere('due_date', '>=', now());
                        });
                    break;
                case 'overdue':
                    $query->whereRaw('amount_paid < amount_due')
                        ->where('due_date', '<', now());
                    break;
                case 'partial':
                    $query->where('amount_paid', '>', 0)
                        ->whereRaw('amount_paid < amount_due');
                    break;
            }
        }

        // Apply amount filter
        if (request('amount_min')) {
            $query->where('amount_due', '>=', request('amount_min'));
        }

        // Apply sorting
        $sortField = request('sort', 'created_at');
        $sortDirection = request('direction', 'desc');

        switch ($sortField) {
            case 'enumeration':
                $query->join('enumerations', 'enumeration_payments.enumeration_id', '=', 'enumerations.id')
                    ->orderBy('enumerations.reference_number', $sortDirection)
                    ->select('enumeration_payments.*'); // Prevent column conflicts
                break;
            case 'amount_due':
                $query->orderBy('amount_due', $sortDirection);
                break;
            case 'due_date':
                $query->orderBy('due_date', $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        // Paginate results
        $enumerationPayments = $query->paginate(25);

        // Calculate statistics
        $stats = [
            'paid_count' => $payment->enumerationPayments()
                ->whereRaw('amount_paid >= amount_due')
                ->count(),
            'pending_count' => $payment->enumerationPayments()
                ->whereRaw('amount_paid < amount_due')
                ->where(function ($q) {
                    $q->whereNull('due_date')
                        ->orWhere('due_date', '>=', now());
                })
                ->count(),
            'overdue_count' => $payment->enumerationPayments()
                ->whereRaw('amount_paid < amount_due')
                ->where('due_date', '<', now())
                ->count(),
            'total_collected' => $payment->enumerationPayments()->sum('amount_paid'),
            'total_outstanding' => $payment->enumerationPayments()
                ->selectRaw('SUM(amount_due - amount_paid) as outstanding')
                ->value('outstanding') ?? 0,
        ];

        return view('admin.projects.payments.enumerations.index', compact('payment', 'enumerationPayments', 'stats'));
    }

    /**
     * Display the specified enumeration payment
     */
    public function showEnumerationPayment(EnumerationPayment $enumerationPayment)
    {
        // Load all necessary relationships
        $enumerationPayment->load([
            'projectPayment.project.customer',
            'enumeration.enumerationData.projectField',
            'paymentTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return view('admin.projects.payments.enumerations.show', compact('enumerationPayment'));
    }

    /**
     * Record a payment against an enumeration payment
     */
    public function recordPayment(Request $request, EnumerationPayment $enumerationPayment)
    {
        $outstanding = $enumerationPayment->amount_due - $enumerationPayment->amount_paid;

        $request->validate([
            'amount' => "required|numeric|min:0.01|max:{$outstanding}",
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $projectPayment = $enumerationPayment->projectPayment;

            $amount = (float) $request->amount;
            $amountDue = (float) $enumerationPayment->amount_due;

            if ($projectPayment && !$projectPayment->allowsPartialPayments()) {
                if (bccomp($amount, $amountDue, 2) !== 0) {
                    return redirect()->back()->with(
                        'error',
                        'Partial payments are not allowed. Please pay the full amount of ₦' . number_format($amountDue, 2) . '.'
                    );
                }
            }

            // Create the payment transaction
            PaymentTransaction::create([
                'enumeration_payment_id' => $enumerationPayment->id,
                'recorded_by_user_id' => Auth::user()->id,
                'amount' => $request->amount,
                'type' => 'payment',
                'status' => 'success',
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'payment_source' => 'manual',
                'transaction_date' => $request->transaction_date,
                'notes' => $request->notes,
            ]);

            // Update the enumeration payment amount_paid
            $enumerationPayment->increment('amount_paid', $request->amount);
            $enumerationPayment->updatePaymentStatus();

            DB::commit();

            return redirect()->back()->with('success', 'Payment of ₦' . number_format($request->amount, 2) . ' recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }
}
