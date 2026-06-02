<?php

namespace NewsTech\Core\Support;

class SeoData
{
    /**
     * @param  array<int, array<string, mixed>>  $structuredData
     * @param  array<int, array{name: string, url: string}>  $breadcrumbs
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $canonicalUrl,
        public string $robots = 'index,follow',
        public ?string $openGraphTitle = null,
        public ?string $openGraphDescription = null,
        public ?string $openGraphImage = null,
        public string $twitterCard = 'summary_large_image',
        public ?string $twitterTitle = null,
        public ?string $twitterDescription = null,
        public ?string $twitterImage = null,
        public array $structuredData = [],
        public array $breadcrumbs = [],
    ) {}

    public static function make(string $title, string $description, string $canonicalUrl): self
    {
        return new self($title, $description, $canonicalUrl);
    }

    public function robots(string $robots): self
    {
        $this->robots = $robots;

        return $this;
    }

    public function openGraph(?string $title = null, ?string $description = null, ?string $image = null): self
    {
        $this->openGraphTitle = $title;
        $this->openGraphDescription = $description;
        $this->openGraphImage = $image;

        return $this;
    }

    public function twitter(string $card, ?string $title = null, ?string $description = null, ?string $image = null): self
    {
        $this->twitterCard = $card;
        $this->twitterTitle = $title;
        $this->twitterDescription = $description;
        $this->twitterImage = $image;

        return $this;
    }

    /**
     * @param  array<int, array<string, mixed>>  $structuredData
     */
    public function structuredData(array $structuredData): self
    {
        $this->structuredData = $structuredData;

        return $this;
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $breadcrumbs
     */
    public function breadcrumbs(array $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    public function resolvedOpenGraphTitle(): string
    {
        return $this->openGraphTitle ?? $this->title;
    }

    public function resolvedOpenGraphDescription(): string
    {
        return $this->openGraphDescription ?? $this->description;
    }

    public function resolvedTwitterTitle(): string
    {
        return $this->twitterTitle ?? $this->title;
    }

    public function resolvedTwitterDescription(): string
    {
        return $this->twitterDescription ?? $this->description;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function breadcrumbStructuredData(): ?array
    {
        if ($this->breadcrumbs === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($this->breadcrumbs)
                ->values()
                ->map(function (array $breadcrumb, int $index): array {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $breadcrumb['name'],
                        'item' => $breadcrumb['url'],
                    ];
                })
                ->all(),
        ];
    }
}
