<?php

namespace NewsTech\Media\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use NewsTech\Core\Support\MediaManager;
use NewsTech\Media\Http\Requests\StoreMediaRequest;
use NewsTech\Media\Http\Requests\UpdateMediaRequest;
use NewsTech\Media\Models\Media;
use NewsTech\Media\Repositories\MediaRepository;
use NewsTech\Media\Support\MediaLibraryManager;

class MediaController
{
    public function __construct(
        protected MediaRepository $media,
        protected MediaLibraryManager $library,
        protected MediaManager $storage
    ) {}

    public function index(): View
    {
        $mediaItems = $this->media->orderedQuery()->paginate(12);

        return view('newstech-admin::media.index', [
            'mediaItems' => $mediaItems,
            'mediaLibraryItems' => $mediaItems->getCollection()
                ->map(fn (Media $media): array => $this->serializeMedia($media))
                ->all(),
            'mediaPaginationHtml' => $mediaItems->onEachSide(1)->links()->toHtml(),
            'mediaCount' => Media::query()->count(),
            'imageCount' => Media::query()
                ->where('mime_type', 'like', 'image/%')
                ->count(),
        ]);
    }

    public function store(StoreMediaRequest $request): RedirectResponse|JsonResponse
    {
        $mediaItems = collect($this->extractFiles($request))
            ->map(fn (UploadedFile $file): Media => $this->library->upload(
                $file,
                $request->validated(),
                auth('admin')->id()
            ))
            ->values();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mediaItems->count() === 1
                    ? 'Media uploaded successfully.'
                    : 'Media uploaded successfully. Review each new item below.',
                'media_items' => $mediaItems
                    ->map(fn (Media $media): array => $this->serializeMedia($media))
                    ->all(),
            ]);
        }

        return redirect()
            ->route('admin.newstech.media.index')
            ->with('media_status', $mediaItems->count() === 1
                ? 'Media uploaded successfully.'
                : 'Media uploaded successfully.');
    }

    public function edit(Media $media): View
    {
        return view('newstech-admin::media.edit', [
            'media' => $media,
            'previewUrl' => $media->resolvedUrl(),
        ]);
    }

    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse|JsonResponse
    {
        $media = $this->library->updateMetadata($media, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Media details updated successfully.',
                'media' => $this->serializeMedia($media),
            ]);
        }

        return redirect()
            ->route('admin.newstech.media.index')
            ->with('media_status', 'Media details updated successfully.');
    }

    public function destroy(Media $media): RedirectResponse|JsonResponse
    {
        $serializedMedia = $this->serializeMedia($media);
        $this->library->delete($media);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Media deleted successfully.',
                'media' => $serializedMedia,
            ]);
        }

        return redirect()
            ->route('admin.newstech.media.index')
            ->with('media_status', 'Media deleted successfully.');
    }

    public function pickerUpload(StoreMediaRequest $request): JsonResponse
    {
        $media = $this->library->upload(
            $request->file('file'),
            $request->validated(),
            auth('admin')->id()
        );

        return response()->json([
            'message' => 'Media uploaded successfully.',
            'media' => $this->serializeMedia($media),
        ]);
    }

    /**
     * @return array<int, UploadedFile>
     */
    protected function extractFiles(StoreMediaRequest $request): array
    {
        if ($request->hasFile('files')) {
            /** @var array<int, UploadedFile> $files */
            $files = $request->file('files', []);

            return $files;
        }

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return [$file];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeMedia(Media $media): array
    {
        return [
            'id' => $media->getKey(),
            'path' => $media->path,
            'url' => $media->resolvedUrl(),
            'filename' => $media->filename,
            'original_name' => $media->original_name ?: $media->filename,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'size' => $media->size,
            'alt_text' => $media->alt_text,
            'caption' => $media->caption,
            'created_at' => $media->created_at?->toIso8601String(),
            'created_at_label' => $media->created_at?->format('M d, Y H:i'),
            'is_image' => $media->isImage(),
            'update_url' => route('admin.newstech.media.update', $media),
            'delete_url' => route('admin.newstech.media.destroy', $media),
            'edit_url' => route('admin.newstech.media.edit', $media),
        ];
    }
}
