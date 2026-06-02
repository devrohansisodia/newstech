<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Admin\Http\Requests\StoreSessionRequest;

class SessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (auth(config('newstech-admin.auth.guard'))->check()) {
            return redirect()->route(config('newstech-admin.auth.redirect_to'));
        }

        return view('newstech-admin::auth.login');
    }

    public function store(StoreSessionRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $request->user(config('newstech-admin.auth.guard'))?->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->intended(route(config('newstech-admin.auth.redirect_to')));
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth(config('newstech-admin.auth.guard'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(config('newstech-admin.auth.login_route'));
    }
}
