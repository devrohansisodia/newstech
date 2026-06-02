<?php

namespace NewsTech\Core\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaManager
{
    public function store(UploadedFile $file, ?string $path = null, ?string $disk = null): string
    {
        return $file->store(
            $path ?? $this->defaultPath(),
            $disk ?? $this->defaultDisk()
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function imageValidationRules(bool $required = true): array
    {
        $rules = ['file'];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        $rules[] = 'mimes:'.implode(',', $this->allowedImageMimeTypes());
        $rules[] = 'max:'.$this->maxUploadSize();

        return $rules;
    }

    /**
     * @param  array<int, string>|null  $allowedExtensions
     * @return array<int, mixed>
     */
    public function fileValidationRules(?array $allowedExtensions = null, bool $required = true): array
    {
        $rules = ['file'];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        if ($allowedExtensions !== null && $allowedExtensions !== []) {
            $rules[] = 'mimes:'.implode(',', $allowedExtensions);
        }

        $rules[] = 'max:'.$this->maxUploadSize();

        return $rules;
    }

    public function url(string $path, ?string $disk = null): string
    {
        return Storage::disk($disk ?? $this->defaultDisk())->url($path);
    }

    public function delete(?string $path, ?string $disk = null): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }

        return Storage::disk($disk ?? $this->defaultDisk())->delete($path);
    }

    public function defaultDisk(): string
    {
        return (string) config('newstech.media.disk', 'public');
    }

    public function defaultPath(): string
    {
        return trim((string) config('newstech.media.path', 'newstech/media'), '/');
    }

    /**
     * @return array<int, string>
     */
    public function allowedImageMimeTypes(): array
    {
        return config('newstech.media.allowed_image_mime_types', ['jpg', 'jpeg', 'png', 'webp']);
    }

    public function maxUploadSize(): int
    {
        return (int) config('newstech.media.max_upload_size', 5120);
    }
}
