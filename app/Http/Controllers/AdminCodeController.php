<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Enumeration;
use App\Models\Activity;
use App\Models\Batch;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
     * Generate a unique reference number for the enumeration.
     */
    private function generateReferenceNumber()
    {
        $timestamp = now()->format('YmdHisv');
        $uniqueId = strtoupper(Str::random(3));
        return $timestamp . $uniqueId;
    }

    /**
     * Display the specified batch of codes.
     */
    public function showBatch(Project $project, Batch $batch)
    {
        $batch->load(['codes' => function ($query) {
            $query->orderBy('is_used', 'asc')->orderBy('created_at', 'asc');
        }]);

        $codes = $batch->codes()->paginate(50);

        return view('admin.projects.codes.show', compact('project', 'batch', 'codes'));
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

function slug($text)
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}
