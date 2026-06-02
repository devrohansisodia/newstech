<?php

namespace NewsTech\Bookmark\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use NewsTech\Bookmark\Http\Requests\StoreBookmarkFolderRequest;
use NewsTech\Bookmark\Repositories\BookmarkFolderRepository;

class BookmarkFolderController extends Controller
{
    public function __construct(protected BookmarkFolderRepository $folders) {}

    public function store(StoreBookmarkFolderRequest $request): RedirectResponse
    {
        $reader = $request->user(config('newstech-reader.auth.guard'));

        if (! $reader) {
            abort(403);
        }

        $this->folders->createForReader($reader, $request->validated('name'));

        return back()->with('bookmark_status', 'Bookmark folder created.');
    }
}
