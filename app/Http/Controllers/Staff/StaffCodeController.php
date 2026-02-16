<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Batch;
use Illuminate\Support\Str;
use App\Jobs\GenerateBatchCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StaffCodeController extends Controller
{
    /**
     * Ensure the authenticated staff belongs to the same customer as the project
     */
    private function checkProjectAccess(Project $project)
    {
        $user = Auth::guard('staff')->user();
        if ($project->customer_id !== $user->customer_id) {
            abort(403, 'Unauthorized access to this project.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        $batches = $project->batches()
            ->withCount(['codes', 'codes as used_codes_count' => function ($query) {
                $query->where('is_used', true);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.projects.codes.index', compact('project', 'batches'));
    }

    /**
     * Show the form for creating a new batch of codes.
     */
    public function create(Project $project)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        return view('staff.projects.codes.create', compact('project'));
    }

    /**
     * Store a newly created batch of codes.
     */
    public function storeBatch(Request $request, Project $project)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        $request->validate([
            'count' => 'required|integer|min:1|max:5000',
        ]);

        $batch = DB::transaction(function () use ($request, $project) {
            $batch = $project->batches()->create([
                'number' => $project->batches()->count() + 1,
                'code' => strtoupper(Str::random(8)),
                'status' => 'pending',
                'total_codes' => $request->count,
            ]);

            return $batch;
        });

        GenerateBatchCodes::dispatch($project, $batch, $request->count);

        return redirect()->route('staff.projects.codes.index', $project)
            ->with('success', 'Batch generation has started in the background!');
    }

    /**
     * Display the specified batch of codes.
     */
    public function showBatch(Project $project, Batch $batch)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        $batch->load(['codes' => function ($query) {
            $query->orderBy('is_used', 'asc')->orderBy('created_at', 'asc');
        }]);

        $codes = $batch->codes()
            ->orderBy('is_used', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return view('staff.projects.codes.show', compact('project', 'batch', 'codes'));
    }

    /**
     * Show a print-friendly view of all codes in a batch.
     */
    public function printBatch(Project $project, Batch $batch)
    {
        $this->checkProjectAccess($project);

        $batch->load(['codes' => function ($query) {
            $query->orderBy('is_used', 'asc')->orderBy('created_at', 'asc');
        }]);

        $codes = $batch->codes()
            ->orderBy('is_used', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('staff.projects.codes.print', compact('project', 'batch', 'codes'));
    }

    /**
     * Check the status of a batch (AJAX).
     */
    public function checkStatus(Project $project, Batch $batch)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        return response()->json([
            'status' => $batch->status,
            'codes_count' => $batch->codes()->count(),
            'total_codes' => $batch->total_codes,
            'is_completed' => $batch->status === 'completed'
        ]);
    }
}
