<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt view.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user('staff')->hasVerifiedEmail()
            ? redirect()->intended(route('staff.dashboard'))
            : view('staff.auth.verify-email');
    }
}
