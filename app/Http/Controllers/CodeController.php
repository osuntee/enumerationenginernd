<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Enumeration;
use App\Models\Activity;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        // Find the project by ID and get its batches
    }

    /**
     * Show the form for creating a new batch of codes.
     */
    public function create($id)
    {
        // Breate a new batch
    }

    /**
     * Store a newly created enumeration entry in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Get validation rules from the project
        $rules = $project->getValidationRules();

        // Add additional enumeration-specific rules
        $rules = array_merge($rules, [
            'enumerated_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
        ]);

        $request->validate($rules);

        DB::transaction(function () use ($request, $project) {
            $ref = $this->generateReferenceNumber();
            $appUrl = config('app.url');

            $url = "{$appUrl}/verify/{$ref}";

            $qrCode = new QrCode(
                data: $url,
                size: 200,
                margin: 10,
            );

            $writer = new PngWriter();
            $qrCodeImage = $writer->write($qrCode);

            $qrCodeBase64 = base64_encode($qrCodeImage->getString());

            // Create the enumeration record
            $enumeration = Enumeration::create([
                'project_id' => $project->id,
                'staff_id'   => $request->staff_id,
                'notes'      => $request->notes,
                'longitude'  => $request->longitude,
                'latitude'   => $request->latitude,
                'reference'  => $ref,
                'qrcode'     => $qrCodeBase64,
                'qrcode'     => $qrCodeBase64,
            ]);

            if ($project->requires_verification) {
                $enumeration->update(['is_verified' => false]);
            } else {
                $enumeration->update(['is_verified' => true]);
            }

            // Set field values
            if ($request->has('data') && is_array($request->data)) {
                $enumeration->setFieldValues($request->data);
            }

            // Create one-off payments
            $enumeration->createOneOffPayments();

            Activity::create([
                'staff_id' => $request->staff_id,
                'activity_type' => 'Enumeration',
                'description' => 'New data captured for project ' . $project->name . $enumeration->reference,
            ]);
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'Enumeration data added successfully!');
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
     * Display the specified enumeration entry.
     */
    public function show($id)
    {
        // Display all the QR codes for a specific batch
    }
}

function slug($text)
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}
