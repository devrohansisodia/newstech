<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReaderEmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $reader = $request->user(config('newstech-reader.auth.guard'));

        if ($reader?->hasVerifiedEmail()) {
            return redirect()->route('newstech.account.dashboard');
        }

        $reader?->sendEmailVerificationNotification();

        return back()->with('verification_status', 'Verification link sent!');
    }
}
