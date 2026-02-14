<?php

namespace App\Http\Controllers\Staff\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Code;
use App\Models\Enumeration;
use App\Models\EnumerationPayment;
use App\Models\Activity;
use App\Models\Staff;
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

        // Add additional enumeration-specific rules
        $rules = array_merge($rules, [
            'longitude' => 'required|string',
            'latitude' => 'required|string',
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());

            return response()->json([
                'message' => $errorString
            ], 403);
        }

        DB::transaction(function () use ($request, $project) {
            $user = Auth::user();

            $ref = $request->input('reference');
            $qrCodeBase64 = null;


            if ($ref) {
                $code = Code::where('reference', $ref)->first();

                if (!$code) {
                    return response()->json([
                        'status' => 'Request failed',
                        'message' => 'Invalid QR code'
                    ], 403);
                }

                if ($code->is_used) {
                    return response()->json([
                        'status' => 'Request failed',
                        'message' => 'QR code has already been used'
                    ], 403);
                }

                $code->markAsUsed();

                $qrCodeBase64 = $code->qrcode;
            } else {
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
            }

            // Create the enumeration record
            $enumeration = Enumeration::create([
                'project_id' => $project->id,
                'staff_id'   => $user->id,
                'notes'      => $request->notes,
                'longitude'  => $request->longitude,
                'latitude'   => $request->latitude,
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

            /// Create one-off payments
            $enumeration->createOneOffPayments();

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
        $records = Enumeration::where('project_id', $project->id)
            ->where('staff_id', $user->id)
            ->with('enumerationData.projectField')
            ->get();

        return response()->json([
            'status' => 'Request successful',
            'records' => $records,
        ], 200);
    }

    public function verify($ref)
    {
        $enumeration = Enumeration::where('reference', $ref)
            ->with('enumerationData.projectField')
            ->first();

        if (!$enumeration) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Enumeration record not found'
            ], 403);
        }

        $payments = EnumerationPayment::where('enumeration_id', $enumeration->id)->get();
        $project = Project::find($enumeration->project_id);
        $staff = Staff::find($enumeration->staff_id);

        $user = Auth::user();

        Activity::create([
            'staff_id' => $user->id,
            'activity_type' => 'Verification',
            'description' => 'QR code verification successful for enumeration in: ' . $project->name . ' - ' . $enumeration->reference,
        ]);

        return response()->json([
            'status' => 'Request successful',
            'project' => $project,
            'staff' => $staff,
            'enumeration' => $enumeration,
            'payments' => $payments,
        ], 200);
    }

    public function check($ref)
    {
        $code = Code::where('reference', $ref)
            ->first();

        if (!$code) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Invalid QR code'
            ], 403);
        }

        if ($code->is_used) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'QR code has already been used'
            ], 403);
        }

        return response()->json([
            'status' => 'Request successful',
            'code' => $code,
        ], 200);
    }
}
