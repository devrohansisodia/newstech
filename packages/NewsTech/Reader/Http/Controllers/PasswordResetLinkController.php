<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use NewsTech\Reader\Http\Requests\StoreReaderPasswordResetLinkRequest;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('newstech-frontend::readers.forgot-password');
    }

    public function store(StoreReaderPasswordResetLinkRequest $request): RedirectResponse
    {
        $status = Password::broker(config('newstech-reader.auth.password_broker'))
            ->sendResetLink($request->only('email'));

        return back()->with('reader_password_status', __($status));
    }
}
