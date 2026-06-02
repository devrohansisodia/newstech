<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use NewsTech\Reader\Http\Requests\ResetReaderPasswordRequest;

class NewReaderPasswordController extends Controller
{
    public function create(string $token): View
    {
        return view('newstech-frontend::readers.reset-password', [
            'token' => $token,
            'email' => request()->string('email')->toString(),
        ]);
    }

    public function store(ResetReaderPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker(config('newstech-reader.auth.password_broker'))->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($reader, string $password): void {
                $reader->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($reader));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('newstech.readers.login')
            ->with('reader_password_status', __($status));
    }
}
