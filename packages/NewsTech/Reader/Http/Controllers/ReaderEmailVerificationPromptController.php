<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ReaderEmailVerificationPromptController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $reader = auth(config('newstech-reader.auth.guard'))->user();

        if ($reader?->hasVerifiedEmail()) {
            return redirect()->route('newstech.account.dashboard');
        }

        return view('newstech-frontend::readers.verify-email', [
            'reader' => $reader,
        ]);
    }
}
