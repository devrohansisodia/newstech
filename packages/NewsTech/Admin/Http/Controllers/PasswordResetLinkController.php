<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use NewsTech\Admin\Http\Requests\SendAdminPasswordResetLinkRequest;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('newstech-admin::auth.forgot-password');
    }

    public function store(SendAdminPasswordResetLinkRequest $request): RedirectResponse
    {
        $status = Password::broker(config('newstech-admin.auth.password_broker'))
            ->sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withInput()
                ->withErrors(['email' => __($status)]);
        }

        return back()->with('status', __($status));
    }
}
