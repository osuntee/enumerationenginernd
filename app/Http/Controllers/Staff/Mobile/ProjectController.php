<?php

namespace App\Http\Controllers\Staff\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Enumeration;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ProjectController extends Controller
{
    public function index($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Project not found'
            ], 403);
        }

        $fields = $project->projectFields()->get();
        $payments = $project->projectPayments()->get();

        return response()->json([
            'status' => 'Request successful',
            'project' => $project,
            'fields' => $fields,
            'payments' => $payments,
        ], 200);
    }

    public function enumerate(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Project not found'
            ], 403);
        }

        // Get validation rules from the project
        $rules = $project->getValidationRules();

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());

            return response()->json([
                'message' => $errorString
            ], 403);
        }

        DB::transaction(function () use ($request, $project) {
            $user = Auth::user();

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
                'staff_id'   => $user->id,
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
                'staff_id' => $user->id,
                'activity_type' => 'Enumeration',
                'description' => 'New data captured for project ' . $project->name . ' - ' . $enumeration->reference,
            ]);
        });

        return response()->json([
            'status' => 'Request successful',
        ], 200);
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

    public function records($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Project not found'
            ], 403);
        }

        $user = Auth::user();
        $records = Enumeration::where('project_id', $project->id)->where('staff_id', $user->id)->get();

        return response()->json([
            'status' => 'Request successful',
            'records' => $records,
        ], 200);
    }
}
