<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Bookmark\Repositories\BookmarkRepository;
use NewsTech\Reader\Http\Requests\UpdateReaderProfileRequest;

class ReaderAccountController extends Controller
{
    public function __construct(protected BookmarkRepository $bookmarks) {}

    public function dashboard(): View
    {
        $reader = auth(config('newstech-reader.auth.guard'))->user();

        return view('newstech-frontend::account.dashboard', [
            'reader' => $reader,
            'bookmarkCount' => $reader ? $this->bookmarks->countForReader($reader) : 0,
            'folderCount' => $reader ? $reader->bookmarkFolders()->count() : 0,
            'historyCount' => $reader ? $reader->readingHistory()->count() : 0,
        ]);
    }

    public function editProfile(): View
    {
        return view('newstech-frontend::account.profile', [
            'reader' => auth(config('newstech-reader.auth.guard'))->user(),
        ]);
    }

    public function updateProfile(UpdateReaderProfileRequest $request): RedirectResponse
    {
        $reader = $request->user(config('newstech-reader.auth.guard'));
        $validated = $request->validated();

        $reader?->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        if (filled($validated['password'] ?? null)) {
            $reader?->forceFill([
                'password' => $validated['password'],
            ]);
        }

        $reader?->save();

        return redirect()
            ->route('newstech.account.profile')
            ->with('account_status', 'Your reader profile has been updated.');
    }
}
