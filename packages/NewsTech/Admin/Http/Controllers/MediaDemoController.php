<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NewsTech\Core\Support\MediaManager;

class MediaDemoController
{
    public function __construct(protected MediaManager $mediaManager) {}

    public function index(): View
    {
        return view('newstech-admin::media.demo');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'upload' => $this->mediaManager->imageValidationRules(),
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.newstech.foundation.media-demo.index')
                ->withErrors($validator)
                ->withInput();
        }

        $storedPath = $this->mediaManager->store($request->file('upload'));

        return redirect()
            ->route('admin.newstech.foundation.media-demo.index')
            ->with('media_demo_upload', [
                'disk' => $this->mediaManager->defaultDisk(),
                'path' => $storedPath,
                'url' => $this->mediaManager->url($storedPath),
            ]);
    }
}
