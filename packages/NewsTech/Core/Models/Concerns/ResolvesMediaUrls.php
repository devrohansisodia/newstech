<?php

namespace NewsTech\Core\Models\Concerns;

use NewsTech\Core\Support\MediaManager;

trait ResolvesMediaUrls
{
    protected function resolveMediaUrl(?string $path): ?string
    {
        $resolvedPath = is_string($path) ? trim($path) : '';

        if ($resolvedPath === '') {
            return null;
        }

        if (filter_var($resolvedPath, FILTER_VALIDATE_URL)) {
            return $resolvedPath;
        }

        return app(MediaManager::class)->url($resolvedPath);
    }
}
