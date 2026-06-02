<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VerifyReaderEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $reader = $request->user(config('newstech-reader.auth.guard'));

        if (
            ! $reader
            || ! hash_equals((string) $request->route('id'), (string) $reader->getKey())
            || ! hash_equals((string) $request->route('hash'), sha1($reader->getEmailForVerification()))
        ) {
            abort(403);
        }

        if (! $reader->hasVerifiedEmail()) {
            $reader->markEmailAsVerified();
            event(new Verified($reader));
        }

        return redirect()
            ->route('newstech.account.dashboard')
            ->with('verification_status', 'Your email address has been verified.');
    }
}
