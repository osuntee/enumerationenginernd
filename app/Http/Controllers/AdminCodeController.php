<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Enumeration;
use App\Models\Activity;
use App\Models\Batch;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
            'count' => 'required|integer|min:1|max:1000',
        ]);

        DB::transaction(function () use ($request, $project) {
            $batch = $project->batches()->create([
                'number' => $project->batches()->count() + 1,
                'code' => strtoupper(Str::random(8)),
            ]);

            $appUrl = config('app.url');

            for ($i = 0; $i < $request->count; $i++) {
                $ref = $this->generateReferenceNumber();
                $url = "{$appUrl}/verify/{$ref}";

                $qrCode = new QrCode(
                    data: $url,
                    size: 200,
                    margin: 10,
                );

                $writer = new PngWriter();
                $qrCodeImage = $writer->write($qrCode);
                $qrCodeBase64 = base64_encode($qrCodeImage->getString());

                $batch->codes()->create([
                    'project_id' => $project->id,
                    'reference' => $ref,
                    'qrcode' => $qrCodeBase64,
                    'is_used' => false,
                ]);
            }

            Activity::create([
                'activity_type' => 'Batch Creation',
                'description' => "Created a new batch of {$request->count} codes for project {$project->name}",
            ]);
        });

        return redirect()->route('projects.codes.index', $project)
            ->with('success', 'Batch created successfully!');
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
}

function slug($text)
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}
