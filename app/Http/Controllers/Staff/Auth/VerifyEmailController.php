<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated staff user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user('staff')->hasVerifiedEmail()) {
            return redirect()->intended(route('staff.dashboard') . '?verified=1');
        }

        if ($request->user('staff')->markEmailAsVerified()) {
            event(new Verified($request->user('staff')));
        }

        return redirect()->intended(route('staff.dashboard') . '?verified=1');
    }
}
