<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Enumeration;
use App\Models\Activity;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffEnumerationController extends Controller
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
     * Show the form for creating a new enumeration entry.
     */
    public function create(Project $project)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        if ($project->pre_generate) {
            return redirect()->back()->with('error', 'This project is set to pre-generate QR Codes. A pre-generated QR code must be scanned for data capture. Please use the mobile app to capture data for this project.');
        }

        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('staff.enumeration.create', compact('project'));
    }

    /**
     * Store a newly created enumeration entry in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

        // Get validation rules from the project
        $rules = $project->getValidationRules();

        // Add additional enumeration-specific rules
        $rules = array_merge($rules, [
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

            $staff = Auth::guard('staff')->user();

            // Create the enumeration record
            $enumeration = Enumeration::create([
                'project_id' => $project->id,
                'staff_id'   => $staff->id,
                'reference'  => $ref,
                'qrcode'     => $qrCodeBase64,
                'notes'      => $request->notes,
                'longitude'  => $request->longitude,
                'latitude'   => $request->latitude,
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
                'staff_id' => $staff->id,
                'activity_type' => 'Enumeration',
                'description' => 'New data captured for project ' . $project->name . $enumeration->reference,
            ]);
        });

        return redirect()->route('staff.projects.show', $project)
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
    public function show(Enumeration $enumeration)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($enumeration->project);

        $project = $enumeration->project;
        $enumeration->load(['enumerationData.projectField']);
        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('staff.enumeration.show', compact('project', 'enumeration'));
    }

    /**
     * Show the form for editing the specified enumeration entry.
     */
    public function edit(Enumeration $enumeration)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($enumeration->project);

        $project = $enumeration->project;
        $enumeration->load(['enumerationData.projectField']);
        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('staff.enumeration.edit', compact('project', 'enumeration'));
    }

    /**
     * Update the specified enumeration entry in storage.
     */
    public function update(Request $request, Enumeration $enumeration)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($enumeration->project);

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

        return redirect()->route('staff.projects.show', $project)
            ->with('success', 'Enumeration data updated successfully!');
    }

    /**
     * Update the location data of specified enumeration entry in storage.
     */
    public function location(Request $request, Enumeration $enumeration)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($enumeration->project);

        $validated = $request->validate([
            'longitude' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'string', 'max:255'],
        ]);

        $enumeration->update([
            'longitude' => $validated['longitude'],
            'latitude' => $validated['latitude'],
        ]);

        return redirect()->route('projects.enumeration.edit', $enumeration)
            ->with('success', 'Enumeration data updated successfully!');
    }

    /**
     * Remove the specified enumeration entry from storage.
     */
    public function destroy(Enumeration $enumeration)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($enumeration->project);

        $project = $enumeration->project;
        $enumeration->delete();

        return redirect()->route('staff.projects.show', $project)
            ->with('success', 'Enumeration data deleted successfully!');
    }

    /**
     * Toggle verification status of an enumeration
     */
    public function toggleVerification(Enumeration $enumeration)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($enumeration->project);

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
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

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
                return $this->exportCsv($project, $enumerations, $fields);
            default:
                return $this->exportCsv($project, $enumerations, $fields);
        }
    }

    /**
     * Export enumeration data as CSV
     */
    private function exportCsv($project, $enumerations, $fields)
    {
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

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
        // Ensure the staff belongs to the same customer as the project
        $this->checkProjectAccess($project);

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
