<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Batch;
use Illuminate\Support\Str;
use App\Jobs\GenerateBatchCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $batches = $project->batches()
            ->withCount(['codes', 'codes as used_codes_count' => function ($query) {
                $query->where('is_used', true);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.projects.codes.index', compact('project', 'batches'));
    }

    /**
     * Show the form for creating a new batch of codes.
     */
    public function create(Project $project)
    {
        return view('admin.projects.codes.create', compact('project'));
    }

    /**
     * Store a newly created batch of codes.
     */
    public function storeBatch(Request $request, Project $project)
    {
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

        return redirect()->route('projects.codes.index', $project)
            ->with('success', 'Batch generation has started in the background!');
    }

    /**
     * Display the specified batch of codes.
     */
    public function showBatch(Request $request, Project $project, Batch $batch)
    {
        $isPrint = $request->boolean('print');

        $batch->load(['codes' => function ($query) {
            $query->orderBy('is_used', 'asc')->orderBy('created_at', 'asc');
        }]);

        $codesQuery = $batch->codes()->orderBy('is_used', 'asc')->orderBy('created_at', 'asc');

        $codes = $isPrint ? $codesQuery->get() : $codesQuery->paginate(50);

        return view('admin.projects.codes.show', compact('project', 'batch', 'codes', 'isPrint'));
    }

    /**
     * Check the status of a batch (AJAX).
     */
    public function checkStatus(Project $project, Batch $batch)
    {
        return response()->json([
            'status' => $batch->status,
            'codes_count' => $batch->codes()->count(),
            'total_codes' => $batch->total_codes,
            'is_completed' => $batch->status === 'completed'
        ]);
    }
}
