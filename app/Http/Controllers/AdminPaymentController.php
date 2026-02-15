<?php
// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\EnumerationPayment;
use Illuminate\Http\Request;
use App\Helpers\PaymentHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of project payments.
     */
    public function index(Request $request)
    {
        $query = ProjectPayment::with(['project', 'enumerationPayments']);

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $projectPayments = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->expectsJson()) {
            return response()->json($projectPayments);
        }

        return view('admin.admin.payments.index', compact('projectPayments'));
    }

    /**
     * Show the form for creating a new project payment.
     */
    public function create(Request $request)
    {
        $projects = Project::active()->get();
        $projectId = $request->get('project_id');

        return view('admin.admin.payments.create', compact('projects', 'projectId'));
    }

    /**
     * Store a newly created project payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:one_off,weekly,monthly,yearly',
            'description' => 'nullable|string|max:1000',
            'allow_partial_payments' => 'boolean',
            'payment_type' => 'required|in:manual,gateway,both',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $validated['is_active'] = true;
        $validated['allow_partial_payments'] = $request->boolean('allow_partial_payments');

        $projectPayment = ProjectPayment::create($validated);

        // Generate enumeration payments for existing enumerations if this is a one-off payment
        if ($validated['frequency'] === 'one_off') {
            PaymentHelper::generateNewEnumerationPayments($projectPayment);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Project payment created successfully',
                'data' => $projectPayment->load(['project'])
            ], 201);
        }

        return redirect()->route('admin.payments.show', $projectPayment)
            ->with('success', 'Project payment created successfully');
    }

    /**
     * Display the specified project payment.
     */
    public function show(ProjectPayment $projectPayment, Request $request)
    {
        $projectPayment->load(['project', 'enumerationPayments.enumeration', 'enumerationPayments.paymentTransactions']);

        // Get payment statistics
        $stats = [
            'total_enumerations' => $projectPayment->enumerationPayments()->count(),
            'total_collected' => $projectPayment->getTotalCollected(),
            'total_outstanding' => $projectPayment->getTotalOutstanding(),
            'pending_count' => $projectPayment->enumerationPayments()->pending()->count(),
            'overdue_count' => $projectPayment->enumerationPayments()->overdue()->count(),
            'paid_count' => $projectPayment->enumerationPayments()->paid()->count(),
        ];

        return view('admin.admin.payments.show', compact('projectPayment', 'stats'));
    }

    /**
     * Show the form for editing the specified project payment.
     */
    public function edit(ProjectPayment $projectPayment)
    {
        $projects = Project::active()->get();

        return view('admin.admin.payments.edit', compact('projectPayment', 'projects'));
    }

    /**
     * Update the specified project payment.
     */
    public function update(Request $request, ProjectPayment $projectPayment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:one_off,weekly,monthly,yearly',
            'description' => 'nullable|string|max:1000',
            'allow_partial_payments' => 'boolean',
            'payment_type' => 'required|in:manual,gateway,both',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['allow_partial_payments'] = $request->boolean('allow_partial_payments');
        $validated['is_active'] = $request->boolean('is_active');

        $projectPayment->update($validated);

        return redirect()->route('admin.payments.show', $projectPayment)
            ->with('success', 'Project payment updated successfully');
    }

    /**
     * Remove the specified project payment.
     */
    public function destroy(ProjectPayment $projectPayment, Request $request)
    {
        // Check if there are any payments made
        if ($projectPayment->enumerationPayments()->where('amount_paid', '>', 0)->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete project payment with existing transactions'
                ], 422);
            }

            return back()->with('error', 'Cannot delete project payment with existing transactions');
        }

        $projectPayment->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Project payment deleted successfully']);
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Project payment deleted successfully');
    }

    /**
     * Display enumeration payments for a project.
     */
    public function enumerationPayments(Request $request, $projectId = null)
    {
        $query = EnumerationPayment::with(['enumeration', 'projectPayment', 'paymentTransactions']);

        if ($projectId) {
            $query->whereHas('enumeration', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        // Filter by project payment
        if ($request->filled('project_payment_id')) {
            $query->where('project_payment_id', $request->project_payment_id);
        }

        $enumerationPayments = $query->orderBy('due_date', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($enumerationPayments);
        }

        return view('admin.admin.payments.enumeration-payments', compact('enumerationPayments', 'projectId'));
    }

    /**
     * Record a manual payment for an enumeration.
     */
    public function recordPayment(Request $request, EnumerationPayment $enumerationPayment)
    {
        // Check if manual payments are allowed
        if (!$enumerationPayment->projectPayment->allowsManualPayment()) {
            return response()->json([
                'message' => 'Manual payments are not allowed for this payment type'
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if partial payments are allowed
        if (!$enumerationPayment->allowsPartialPayments()) {
            if ($validated['amount'] != $enumerationPayment->getOutstandingAmount()) {
                return response()->json([
                    'message' => 'Partial payments are not allowed. Full amount required: ' .
                        number_format($enumerationPayment->getOutstandingAmount(), 2)
                ], 422);
            }
        }

        // Check if amount doesn't exceed outstanding balance
        if ($validated['amount'] > $enumerationPayment->getOutstandingAmount()) {
            return response()->json([
                'message' => 'Payment amount cannot exceed outstanding balance of ' .
                    number_format($enumerationPayment->getOutstandingAmount(), 2)
            ], 422);
        }

        DB::beginTransaction();

        try {
            $transaction = $enumerationPayment->recordPayment(
                amount: $validated['amount'],
                paymentMethod: $validated['payment_method'],
                source: 'manual',
                staffId: Auth::id(),
                reference: $validated['reference'],
                notes: $validated['notes']
            );

            DB::commit();

            return response()->json([
                'message' => 'Payment recorded successfully',
                'data' => [
                    'transaction' => $transaction,
                    'enumeration_payment' => $enumerationPayment->fresh(['paymentTransactions'])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a refund for a payment.
     */
    public function refund(Request $request, EnumerationPayment $enumerationPayment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $enumerationPayment->amount_paid,
            'reason' => 'required|string|max:1000',
            'payment_method' => 'required|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $transaction = $enumerationPayment->paymentTransactions()->create([
                'amount' => $validated['amount'],
                'type' => 'refund',
                'payment_method' => $validated['payment_method'],
                'payment_source' => 'manual',
                'recorded_by_staff_id' => Auth::id(),
                'transaction_date' => now(),
                'notes' => 'Refund: ' . $validated['reason'],
            ]);

            $enumerationPayment->updatePaymentStatus();

            DB::commit();

            return response()->json([
                'message' => 'Refund processed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'enumeration_payment' => $enumerationPayment->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Waive a payment.
     */
    public function waive(Request $request, EnumerationPayment $enumerationPayment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($enumerationPayment->status === 'waived') {
            return response()->json([
                'message' => 'Payment is already waived'
            ], 422);
        }

        $enumerationPayment->markAsWaived(Auth::id(), $validated['reason']);

        return response()->json([
            'message' => 'Payment waived successfully',
            'data' => $enumerationPayment->fresh()
        ]);
    }

    /**
     * Generate enumeration payments for existing enumerations.
     */
    public function generatePayments(Request $request, ProjectPayment $projectPayment)
    {
        $validated = $request->validate([
            'due_date' => 'nullable|date',
        ]);

        $generated = PaymentHelper::generateNewEnumerationPayments($projectPayment, $validated['due_date'] ?? null);

        return response()->json([
            'message' => "Generated {$generated} enumeration payments",
            'generated_count' => $generated
        ]);
    }

    /**
     * Get payment statistics for a project.
     */
    public function statistics(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $stats = [
            'total_payments_due' => $project->enumerationPayments()->sum('amount_due'),
            'total_payments_collected' => $project->enumerationPayments()->sum('amount_paid'),
            'total_outstanding' => $project->getTotalPaymentsOutstanding(),
            'payment_counts' => [
                'pending' => $project->enumerationPayments()->pending()->count(),
                'partial' => $project->enumerationPayments()->where('status', 'partial')->count(),
                'paid' => $project->enumerationPayments()->paid()->count(),
                'overdue' => $project->enumerationPayments()->overdue()->count(),
                'waived' => $project->enumerationPayments()->where('status', 'waived')->count(),
            ],
            'by_payment_type' => $project->projectPayments()
                ->withCount('enumerationPayments')
                ->get()
                ->map(function ($payment) {
                    return [
                        'name' => $payment->name,
                        'total_due' => $payment->enumerationPayments()->sum('amount_due'),
                        'total_collected' => $payment->getTotalCollected(),
                        'outstanding' => $payment->getTotalOutstanding(),
                        'count' => $payment->enumeration_payments_count,
                    ];
                })
        ];

        return response()->json($stats);
    }

    /**
     * Bulk generate recurring payments (for scheduled jobs).
     */
    public function generateRecurringPayments(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'frequency' => 'nullable|in:weekly,monthly,yearly',
        ]);

        $query = ProjectPayment::active()->validForDate();

        if (isset($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        if (isset($validated['frequency'])) {
            $query->where('frequency', $validated['frequency']);
        }

        // Exclude one-off payments
        $query->where('frequency', '!=', 'one_off');

        $projectPayments = $query->get();
        $totalGenerated = 0;

        foreach ($projectPayments as $projectPayment) {
            // Find the last due date for this payment type
            $lastDueDate = EnumerationPayment::where('project_payment_id', $projectPayment->id)
                ->max('due_date');

            $nextDueDate = $projectPayment->calculateNextDueDate($lastDueDate);

            // Only generate if the next due date is today or in the past
            if ($nextDueDate->lte(now())) {
                $generated = PaymentHelper::generateNewEnumerationPayments($projectPayment, $nextDueDate);
                $totalGenerated += $generated;
            }
        }

        return response()->json([
            'message' => "Generated {$totalGenerated} recurring payments",
            'generated_count' => $totalGenerated
        ]);
    }
}
