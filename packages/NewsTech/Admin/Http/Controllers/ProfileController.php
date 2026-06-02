<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Admin\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('newstech-admin::profile.edit', [
            'adminUser' => auth(config('newstech-admin.auth.guard'))->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $adminUser = $request->user(config('newstech-admin.auth.guard'));
        $validated = $request->validated();

        $adminUser?->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (filled($validated['password'] ?? null)) {
            $adminUser?->forceFill([
                'password' => $validated['password'],
            ]);
        }

        $adminUser?->save();

        return redirect()
            ->route('admin.newstech.profile.edit')
            ->with('profile_status', 'Your admin profile has been updated.');
    }
}
