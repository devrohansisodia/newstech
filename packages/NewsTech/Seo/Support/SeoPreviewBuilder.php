<?php

namespace NewsTech\Seo\Support;

use NewsTech\Core\Support\MediaManager;

class SeoPreviewBuilder
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function build(array $data): array
    {
        $title = $this->stringValue($data['title'] ?? null);
        $slug = $this->stringValue($data['slug'] ?? null);
        $excerpt = $this->stringValue($data['excerpt'] ?? null);
        $metaTitle = $this->stringValue($data['meta_title'] ?? null);
        $metaDescription = $this->stringValue($data['meta_description'] ?? null);
        $featuredImage = $this->stringValue($data['featured_image'] ?? null);
        $canonicalUrl = $this->resolveCanonicalUrl($data);

        $resolvedTitle = $metaTitle !== ''
            ? $metaTitle
            : $this->fallbackTitle($title);
        $resolvedDescription = $metaDescription !== ''
            ? $metaDescription
            : ($excerpt !== ''
                ? $excerpt
                : (string) config('newstech-seo.default_meta_description'));

        return [
            'title' => $resolvedTitle,
            'url' => $canonicalUrl,
            'description' => $resolvedDescription,
            'social_title' => $resolvedTitle,
            'social_description' => $resolvedDescription,
            'social_image' => $this->resolveMediaUrl($featuredImage),
            'canonical_url' => $canonicalUrl,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveCanonicalUrl(array $data): string
    {
        $explicitCanonical = $this->stringValue($data['canonical_url'] ?? null);

        if ($explicitCanonical !== '') {
            return $explicitCanonical;
        }

        $slug = $this->stringValue($data['slug'] ?? null);
        $type = $this->stringValue($data['type'] ?? null);

        if ($slug === '') {
            return url('/');
        }

        return match ($type) {
            'article' => route('newstech.articles.show', ['slug' => $slug]),
            'page' => route('newstech.pages.show', ['slug' => $slug]),
            default => url('/'.$slug),
        };
    }

    protected function fallbackTitle(string $title): string
    {
        $suffix = trim((string) config('newstech-seo.site_title_suffix'));

        if ($title === '') {
            return config('newstech.brand.name');
        }

        return $suffix !== ''
            ? $title.$suffix
            : $title;
    }

    protected function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    protected function resolveMediaUrl(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return app(MediaManager::class)->url($path);
    }
}
