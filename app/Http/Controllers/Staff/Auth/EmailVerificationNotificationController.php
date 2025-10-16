<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user('staff')->hasVerifiedEmail()) {
            return redirect()->intended(route('staff.dashboard'));
        }

        $request->user('staff')->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
