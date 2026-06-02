<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Reader\Http\Requests\StoreReaderSessionRequest;

class ReaderSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (auth(config('newstech-reader.auth.guard'))->check()) {
            return redirect()->route(config('newstech-reader.auth.redirect_to'));
        }

        return view('newstech-frontend::readers.login');
    }

    public function store(StoreReaderSessionRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $request->user(config('newstech-reader.auth.guard'))?->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->intended(route(config('newstech-reader.auth.redirect_to')));
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth(config('newstech-reader.auth.guard'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('newstech.home');
    }
}
