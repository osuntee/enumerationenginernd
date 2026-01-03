<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Enumeration;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnumerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Store a newly created enumeration entry in storage.
     */
    public function enumerate(Request $request, $code)
    {
        $project = Project::where('code', $code)->firstOrFail();

        if (!$project || !$project->allow_api) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Project not found or API enumeration not enabled.'
            ], 403);
        }

        // Get validation rules from the project
        $rules = $project->getValidationRules();

        // Add additional enumeration-specific rules
        $rules = array_merge($rules, [
            'reference' => 'required|string|max:255',
            'enumerated_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());


            return response()->json([
                'message' => $errorString
            ], 403);
        }

        DB::transaction(function () use ($request, $project) {
            $appUrl = config('app.url');
            $ref = $request->reference;

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
                'reference'  => $ref,
                'qrcode' => $qrCodeBase64,
                'self_enumerated' => 0,
            ]);

            // Set field values
            if ($request->has('data') && is_array($request->data)) {
                $enumeration->setFieldValues($request->data);
            }
        });

        return response()->json([
            'status' => 'Success',
            'message' => 'Enumeration recorded successfully.'
        ], 200);
    }
}
