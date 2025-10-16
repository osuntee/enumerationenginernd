<?php

namespace App\Http\Controllers\Staff\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Staff;
use App\Models\ProjectField;
use App\Models\ProjectPayment;
use App\Models\EnumerationPayment;
use App\Models\PaymentTransaction;
use App\Helpers\PaymentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
}
