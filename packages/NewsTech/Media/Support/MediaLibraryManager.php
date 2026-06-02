<?php

namespace NewsTech\Media\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Core\Models\SystemSetting;
use NewsTech\Core\Support\MediaManager;
use NewsTech\Media\Models\Media;
use NewsTech\Media\Repositories\MediaRepository;

class MediaLibraryManager
{
    public function __construct(
        protected MediaRepository $media,
        protected MediaManager $storage
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function upload(UploadedFile $file, array $attributes = [], ?int $uploadedBy = null): Media
    {
        $storedMedia = $this->storeFile($file);

        /** @var Media $media */
        $media = $this->media->create([
            'disk' => $storedMedia['disk'],
            'path' => $storedMedia['path'],
            'filename' => $storedMedia['filename'],
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $storedMedia['mime_type'],
            'extension' => $storedMedia['extension'],
            'size' => $storedMedia['size'],
            'alt_text' => $attributes['alt_text'] ?? null,
            'caption' => $attributes['caption'] ?? null,
            'uploaded_by' => $uploadedBy,
        ]);

        return $media;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateMetadata(Media $media, array $attributes): Media
    {
        /** @var Media $updated */
        $updated = $this->media->update($media, [
            'alt_text' => $attributes['alt_text'] ?: null,
            'caption' => $attributes['caption'] ?: null,
        ]);

        return $updated;
    }

    public function delete(Media $media): void
    {
        $path = $media->path;
        $disk = $media->disk;

        $media->delete();

        if ($this->fileCanBeDeleted($path, $disk)) {
            $this->storage->delete($path, $disk);
        }
    }

    protected function fileCanBeDeleted(string $path, string $disk): bool
    {
        $hasOtherLiveMedia = Media::query()
            ->whereNull('deleted_at')
            ->where('disk', $disk)
            ->where('path', $path)
            ->exists();

        if ($hasOtherLiveMedia) {
            return false;
        }

        $isUsedByArticle = Article::query()->where('featured_image', $path)->exists();
        $isUsedByAuthor = Author::query()->where('avatar', $path)->exists();
        $isUsedBySettings = SystemSetting::query()
            ->whereIn('key', ['website.identity.logo_path', 'website.identity.footer_logo_path'])
            ->where('value', $path)
            ->exists();

        return ! $isUsedByArticle && ! $isUsedByAuthor && ! $isUsedBySettings;
    }

    /**
     * @return array{disk:string,path:string,filename:string,mime_type:string,extension:string,size:int}
     */
    protected function storeFile(UploadedFile $file): array
    {
        $convertedMedia = $this->storeAsWebpIfSupported($file);

        if ($convertedMedia !== null) {
            return $convertedMedia;
        }

        $path = $this->storage->store($file);

        return [
            'disk' => $this->storage->defaultDisk(),
            'path' => $path,
            'filename' => basename($path),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'extension' => strtolower($file->getClientOriginalExtension()),
            'size' => $file->getSize() ?: 0,
        ];
    }

    /**
     * @return array{disk:string,path:string,filename:string,mime_type:string,extension:string,size:int}|null
     */
    protected function storeAsWebpIfSupported(UploadedFile $file): ?array
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $sourceImage = $this->createImageResource($file);

        if ($sourceImage === null) {
            return null;
        }

        imagepalettetotruecolor($sourceImage);
        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);

        ob_start();
        $conversionSucceeded = imagewebp($sourceImage, null, 85);
        $binary = ob_get_clean();
        imagedestroy($sourceImage);

        if (! $conversionSucceeded || ! is_string($binary) || $binary === '') {
            return null;
        }

        $disk = $this->storage->defaultDisk();
        $directory = trim($this->storage->defaultPath(), '/');
        $filename = Str::random(40).'.webp';
        $path = ($directory !== '' ? $directory.'/' : '').$filename;

        Storage::disk($disk)->put($path, $binary);

        return [
            'disk' => $disk,
            'path' => $path,
            'filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => strlen($binary),
        ];
    }

    protected function createImageResource(UploadedFile $file): mixed
    {
        $realPath = $file->getRealPath();

        if (! is_string($realPath) || $realPath === '') {
            return null;
        }

        $mimeType = $file->getMimeType() ?: $file->getClientMimeType();

        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath) ?: null,
            'image/png' => @imagecreatefrompng($realPath) ?: null,
            default => null,
        };
    }
}
