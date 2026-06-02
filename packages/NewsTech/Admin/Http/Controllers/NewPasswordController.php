<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use NewsTech\Admin\Http\Requests\ResetAdminPasswordRequest;
use NewsTech\Admin\Models\AdminUser;

class NewPasswordController extends Controller
{
    public function create(string $token): View
    {
        return view('newstech-admin::auth.reset-password', [
            'token' => $token,
            'email' => request()->string('email')->toString(),
        ]);
    }

    public function store(ResetAdminPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker(config('newstech-admin.auth.password_broker'))->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (AdminUser $adminUser, string $password): void {
                $adminUser->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($adminUser));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route(config('newstech-admin.auth.login_route'))
            ->with('status', __($status));
    }
}
