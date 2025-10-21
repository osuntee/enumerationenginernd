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

class EnumerationController extends Controller
{
    /**
     * Show the form for creating a new enumeration entry.
     */
    public function create(Project $project)
    {
        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('admin.enumeration.create', compact('project'));
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
                'reference'  => $ref,
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

            Activity::create([
                'staff_id' => $request->staff_id,
                'activity_type' => 'Enumeration',
                'description' => 'New data captured for project ' . $project->name . $enumeration->reference,
            ]);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Enumeration data added successfully!');
        });

        return redirect()->back()->with('error', 'Something went wrong, confirm enumeration was successful');
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
    public function show(Enumeration $enumeration)
    {
        $project = $enumeration->project;
        $enumeration->load(['enumerationData.projectField']);
        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('admin.enumeration.show', compact('project', 'enumeration'));
    }

    /**
     * Update all of the QR codes with the url in the .env file
     */
    public function updateAllQRCodes()
    {
        $appUrl = config('app.url');

        $enumerations = Enumeration::all();

        foreach ($enumerations as $enumeration) {
            $ref = $enumeration->reference;
            $url = "{$appUrl}/verify/{$ref}";

            $qrCode = new QrCode(
                data: $url,
                size: 200,
                margin: 10,
            );

            $writer = new PngWriter();
            $qrCodeImage = $writer->write($qrCode);

            $qrCodeBase64 = base64_encode($qrCodeImage->getString());

            $enumeration->update([
                'qrcode' => $qrCodeBase64,
            ]);
        }

        return redirect()->back()->with('success', 'All QR codes updated successfully!');
    }

    /**
     * Show the form for editing the specified enumeration entry.
     */
    public function edit(Enumeration $enumeration)
    {
        $project = $enumeration->project;
        $enumeration->load(['enumerationData.projectField']);
        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('admin.enumeration.edit', compact('project', 'enumeration'));
    }

    /**
     * Update the specified enumeration entry in storage.
     */
    public function update(Request $request, Enumeration $enumeration)
    {
        $project = $enumeration->project;
        // Get validation rules from the project
        $rules = $project->getValidationRules();

        // Add additional enumeration-specific rules
        $rules = array_merge($rules, [
            'enumerated_at' => 'nullable|date',
            'enumerated_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $request->validate($rules);

        DB::transaction(function () use ($request, $enumeration) {
            // Update enumeration metadata
            $enumeration->update([
                'enumerated_at' => $request->enumerated_at ? \Carbon\Carbon::parse($request->enumerated_at) : $enumeration->enumerated_at,
                'enumerated_by' => $request->enumerated_by,
                'notes' => $request->notes,
            ]);

            // Update field values
            if ($request->has('data') && is_array($request->data)) {
                $enumeration->setFieldValues($request->data);
            }
        });

        return redirect()->route('projects.show', $project)
            ->with('success', 'Enumeration data updated successfully!');
    }

    /**
     * Remove the specified enumeration entry from storage.
     */
    public function destroy(Enumeration $enumeration)
    {
        $project = $enumeration->project;
        $enumeration->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Enumeration data deleted successfully!');
    }

    /**
     * Toggle verification status of an enumeration
     */
    public function toggleVerification(Enumeration $enumeration)
    {
        $enumeration->update([
            'is_verified' => !$enumeration->is_verified
        ]);

        $status = $enumeration->is_verified ? 'verified' : 'unverified';

        return redirect()->back()
            ->with('success', "Enumeration marked as {$status}!");
    }

    /**
     * Export enumeration data
     */
    public function export(Project $project, Request $request)
    {
        $format = $request->get('format', 'csv');

        $enumerations = $project->enumerations()
            ->with(['enumerationData.projectField'])
            ->orderBy('created_at', 'desc')
            ->get();

        $fields = $project->getActiveFields();

        switch ($format) {
            case 'json':
                return $this->exportJson($project, $enumerations, $fields);
            case 'csv':
            default:
                return $this->exportCsv($project, $enumerations, $fields);
        }
    }

    /**
     * Export enumeration data as CSV
     */
    private function exportCsv($project, $enumerations, $fields)
    {
        $filename = slug($project->name) . '_enumeration_data_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($enumerations, $fields) {
            $file = fopen('php://output', 'w');

            // Write header row
            $headerRow = ['ID', 'Enumerated At', 'Enumerated By', 'Verified', 'Notes'];
            foreach ($fields as $field) {
                $headerRow[] = $field->label;
            }
            fputcsv($file, $headerRow);

            // Write data rows
            foreach ($enumerations as $enumeration) {
                $fieldValues = $enumeration->getFieldValues();

                $row = [
                    $enumeration->id,
                    $enumeration->enumerated_at ? $enumeration->enumerated_at->format('Y-m-d H:i:s') : '',
                    $enumeration->enumerated_by,
                    $enumeration->is_verified ? 'Yes' : 'No',
                    $enumeration->notes,
                ];

                foreach ($fields as $field) {
                    $value = $fieldValues[$field->name] ?? '';

                    // Handle array values (checkboxes, etc.)
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $row[] = $value;
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export enumeration data as JSON
     */
    private function exportJson($project, $enumerations, $fields)
    {
        $filename = slug($project->name) . '_enumeration_data_' . now()->format('Y-m-d') . '.json';

        $data = [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'exported_at' => now()->toISOString(),
            ],
            'fields' => $fields->map(function ($field) {
                return [
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'required' => $field->required,
                    'options' => $field->options,
                ];
            }),
            'enumerations' => $enumerations->map(function ($enumeration) {
                return [
                    'id' => $enumeration->id,
                    'enumerated_at' => $enumeration->enumerated_at?->toISOString(),
                    'staff_member' => $enumeration->staff ? $enumeration->staff->name : null,
                    'is_verified' => $enumeration->is_verified,
                    'notes' => $enumeration->notes,
                    'data' => $enumeration->getFieldValues(),
                ];
            })
        ];

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}

function slug($text)
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}
