<?php

namespace NewsTech\Installer\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use NewsTech\Media\Models\Media;

class DemoAssetPublisher
{
    /**
     * @return array{count:int, assets:array<string, string>}
     */
    public function publish(bool $force): array
    {
        $sourceRoot = base_path('packages/NewsTech/Installer/Resources/demo/images');
        $storageRoot = trim((string) config('newstech-installer.demo.storage_directory', 'newstech/demo'), '/');

        if (! File::isDirectory($sourceRoot)) {
            return [
                'count' => 0,
                'assets' => [],
            ];
        }

        $assetMap = [];
        $publishedCount = 0;

        foreach (File::allFiles($sourceRoot) as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $targetPath = $storageRoot.'/'.$relativePath;
            $segments = explode('/', $relativePath);

            if ($force || ! Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->put($targetPath, $file->getContents());
                $publishedCount++;
            }

            $assetKey = collect($segments)
                ->map(function (string $segment, int $index) use ($segments): string {
                    if ($index === count($segments) - 1) {
                        $segment = pathinfo($segment, PATHINFO_FILENAME);
                    }

                    return str($segment)->snake()->toString();
                })
                ->implode('.');

            $assetMap[$assetKey] = $targetPath;

            Media::query()->updateOrCreate(
                [
                    'disk' => 'public',
                    'path' => $targetPath,
                ],
                [
                    'filename' => $file->getFilename(),
                    'original_name' => $file->getFilename(),
                    'mime_type' => File::mimeType($file->getPathname()) ?: 'image/svg+xml',
                    'extension' => $file->getExtension(),
                    'size' => $file->getSize(),
                    'alt_text' => str($file->getFilenameWithoutExtension())->replace(['-', '_'], ' ')->headline()->toString(),
                    'caption' => 'NewsTech demo asset',
                ]
            );
        }

        return [
            'count' => $publishedCount,
            'assets' => $assetMap,
        ];
    }
}
