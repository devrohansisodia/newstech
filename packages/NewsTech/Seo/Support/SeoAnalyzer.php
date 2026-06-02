<?php

namespace NewsTech\Seo\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

class SeoAnalyzer
{
    public function __construct(protected SeoPreviewBuilder $previewBuilder) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function analyze(array $data): SeoScoreResult
    {
        $errors = [];
        $warnings = [];
        $suggestions = [];
        $checklist = [];

        $title = $this->stringValue($data['title'] ?? null);
        $slug = $this->stringValue($data['slug'] ?? null);
        $excerpt = $this->stringValue($data['excerpt'] ?? null);
        $metaTitle = $this->stringValue($data['meta_title'] ?? null);
        $metaDescription = $this->stringValue($data['meta_description'] ?? null);
        $featuredImage = $this->stringValue($data['featured_image'] ?? null);
        $focusKeyword = $this->stringValue($data['focus_keyword'] ?? null);
        $canonicalUrl = $this->stringValue($data['canonical_url'] ?? null);
        $status = $data['status'] ?? null;
        $type = $this->stringValue($data['type'] ?? null);
        $authorName = $this->stringValue($data['author_name'] ?? null);
        $categoryName = $this->stringValue($data['category_name'] ?? null);
        $publishedAt = $this->stringValue($data['published_at'] ?? null);
        $tagNames = collect($data['tag_names'] ?? [])->filter(fn ($tag): bool => is_string($tag) && trim($tag) !== '')->values();

        $contentHtml = $this->stringValue($data['content_html'] ?? ($data['content'] ?? null));
        $dom = $this->loadDom($contentHtml);
        $plainText = $this->extractPlainText($contentHtml, $dom);
        $wordCount = $plainText === '' ? 0 : count(preg_split('/\s+/u', $plainText) ?: []);
        $headingCount = $this->countNodes($dom, '//h2 | //h3');
        $emptyParagraphCount = $this->countEmptyParagraphs($dom);
        $images = $this->extractImages($dom);
        $missingAltImageCount = collect($images)->where('missing_alt', true)->count();
        $links = $this->extractLinks($dom);
        $externalLinks = collect($links)->where('external', true)->values();
        $internalLinks = collect($links)->where('external', false)->values();
        $unsafeExternalLinks = $externalLinks->where('unsafe_rel', true)->count();
        $focusKeywordOccurrences = $focusKeyword !== ''
            ? mb_substr_count(mb_strtolower($plainText), mb_strtolower($focusKeyword))
            : 0;

        $this->evaluateMetaTitle($metaTitle, $errors, $warnings, $checklist);
        $this->evaluateMetaDescription($metaDescription, $errors, $warnings, $checklist);
        $this->evaluateSlug($slug, $errors, $warnings, $checklist);
        $this->evaluateContent($wordCount, $headingCount, $emptyParagraphCount, $warnings, $suggestions, $checklist);
        $this->evaluateFeaturedImage($featuredImage, $warnings, $suggestions, $checklist);
        $this->evaluateImages($missingAltImageCount, $images, $warnings, $suggestions, $checklist);
        $this->evaluateLinks($wordCount, $internalLinks->count(), $externalLinks->count(), $unsafeExternalLinks, $warnings, $suggestions, $checklist);
        $this->evaluateFocusKeyword($focusKeyword, $title, $slug, $metaDescription, $plainText, $focusKeywordOccurrences, $warnings, $suggestions, $checklist);
        $this->evaluateCanonical($canonicalUrl, $warnings, $suggestions, $checklist);
        $this->evaluateStructuredDataReadiness($type, $authorName, $categoryName, $publishedAt, $tagNames->count(), $warnings, $suggestions, $checklist);
        $this->evaluateVisibility($type, $status, $suggestions, $checklist);

        $score = max(0, min(100, 100 - (count($errors) * 15) - (count($warnings) * 8)));
        $grade = match (true) {
            $score >= 80 => 'good',
            $score >= 50 => 'needs_improvement',
            default => 'poor',
        };

        return new SeoScoreResult(
            score: $score,
            grade: $grade,
            errors: $errors,
            warnings: $warnings,
            suggestions: $suggestions,
            checklist: $checklist,
            preview: $this->previewBuilder->build($data),
        );
    }

    /**
     * @param  array<int, SeoIssue>  $errors
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateMetaTitle(string $metaTitle, array &$errors, array &$warnings, array &$checklist): void
    {
        if ($metaTitle === '') {
            $errors[] = new SeoIssue('error', 'meta_title_missing', 'Add a meta title', 'Meta title is missing.', 'meta_title', 'Add a search title around 40 to 60 characters.');
            $checklist[] = $this->check('meta_title', 'Meta title is present', false, 'error', 'Add a meta title before publishing.');

            return;
        }

        $length = mb_strlen($metaTitle);

        if ($length < 40) {
            $warnings[] = new SeoIssue('warning', 'meta_title_short', 'Meta title is short', 'Meta title is shorter than the recommended 40 to 60 characters.', 'meta_title', 'Expand the title so search users understand the page context.');
        } elseif ($length > 60) {
            $warnings[] = new SeoIssue('warning', 'meta_title_long', 'Meta title is long', 'Meta title is longer than the recommended 40 to 60 characters.', 'meta_title', 'Trim the title so it is less likely to be truncated in search results.');
        }

        $checklist[] = $this->check('meta_title', 'Meta title is present', true, 'pass', 'Meta title is available for search snippets.');
    }

    /**
     * @param  array<int, SeoIssue>  $errors
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateMetaDescription(string $metaDescription, array &$errors, array &$warnings, array &$checklist): void
    {
        if ($metaDescription === '') {
            $errors[] = new SeoIssue('error', 'meta_description_missing', 'Add a meta description', 'Meta description is missing.', 'meta_description', 'Add a description around 120 to 160 characters.');
            $checklist[] = $this->check('meta_description', 'Meta description is present', false, 'error', 'Add a concise summary for search and social previews.');

            return;
        }

        $length = mb_strlen($metaDescription);

        if ($length < 120) {
            $warnings[] = new SeoIssue('warning', 'meta_description_short', 'Meta description is short', 'Meta description is shorter than the recommended 120 to 160 characters.', 'meta_description', 'Add a little more detail to improve search snippet context.');
        } elseif ($length > 160) {
            $warnings[] = new SeoIssue('warning', 'meta_description_long', 'Meta description is long', 'Meta description is longer than the recommended 120 to 160 characters.', 'meta_description', 'Trim the description so search engines are less likely to truncate it.');
        }

        $checklist[] = $this->check('meta_description', 'Meta description is present', true, 'pass', 'Meta description is available for search and social previews.');
    }

    /**
     * @param  array<int, SeoIssue>  $errors
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateSlug(string $slug, array &$errors, array &$warnings, array &$checklist): void
    {
        if ($slug === '') {
            $errors[] = new SeoIssue('error', 'slug_missing', 'Add a slug', 'Slug is missing.', 'slug', 'Use a short lowercase slug with hyphens.');
            $checklist[] = $this->check('slug', 'Slug is present', false, 'error', 'Add a URL slug before publishing.');

            return;
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $warnings[] = new SeoIssue('warning', 'slug_format', 'Slug format needs cleanup', 'Slug should stay lowercase and hyphenated.', 'slug', 'Avoid spaces, uppercase letters, and extra punctuation in the slug.');
        }

        if (mb_strlen($slug) > 80) {
            $warnings[] = new SeoIssue('warning', 'slug_long', 'Slug is long', 'Slug is longer than the recommended 80 characters.', 'slug', 'Shorten the slug so it remains readable in search results and shared links.');
        }

        $checklist[] = $this->check('slug', 'Slug is present', true, 'pass', 'Slug is available for the page URL.');
    }

    /**
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateContent(int $wordCount, int $headingCount, int $emptyParagraphCount, array &$warnings, array &$suggestions, array &$checklist): void
    {
        if ($wordCount < 80) {
            $warnings[] = new SeoIssue('warning', 'content_short', 'Content is short', 'Body content is very short for search visibility and editorial depth.', 'content', 'Add more useful body copy before publishing.');
        } elseif ($wordCount < 180) {
            $warnings[] = new SeoIssue('warning', 'content_light', 'Content could be deeper', 'Body content is lighter than the recommended depth for search-friendly pages.', 'content', 'Expand the body with more detail, context, or supporting information.');
        }

        if ($wordCount >= 120 && $headingCount === 0) {
            $warnings[] = new SeoIssue('warning', 'headings_missing', 'Add subheadings', 'Long-form content does not include any H2 or H3 headings.', 'content', 'Break the body into sections with H2 or H3 headings.');
        }

        if ($emptyParagraphCount >= 3) {
            $suggestions[] = new SeoIssue('suggestion', 'empty_paragraphs', 'Remove empty paragraphs', 'The editor content contains several empty paragraphs.', 'content', 'Tighten the spacing so the content stays clean and easier to scan.');
        }

        if ($wordCount > 0 && $wordCount < 250) {
            $suggestions[] = new SeoIssue('suggestion', 'readability_depth', 'Add more supporting detail', 'The draft is readable, but it may benefit from more explanatory detail or examples.', 'content', 'Add context, quotes, or supporting paragraphs where helpful.');
        }

        $checklist[] = $this->check('content_depth', 'Content has enough depth', $wordCount >= 180, $wordCount >= 180 ? 'pass' : 'warning', $wordCount >= 180 ? 'Content length looks healthy.' : 'Add more body text for a stronger SEO foundation.');
        $checklist[] = $this->check('content_headings', 'Content uses subheadings', $headingCount > 0 || $wordCount < 120, ($headingCount > 0 || $wordCount < 120) ? 'pass' : 'warning', $headingCount > 0 ? 'Subheadings are present.' : 'Add H2 or H3 headings for long-form content.');
    }

    /**
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateFeaturedImage(string $featuredImage, array &$warnings, array &$suggestions, array &$checklist): void
    {
        if ($featuredImage === '') {
            $warnings[] = new SeoIssue('warning', 'featured_image_missing', 'Add a featured image', 'Featured image is missing.', 'featured_image', 'Choose a featured image to improve social sharing and richer previews.');
            $checklist[] = $this->check('featured_image', 'Featured image is selected', false, 'warning', 'Select a featured image for better social and SEO coverage.');

            return;
        }

        $suggestions[] = new SeoIssue('suggestion', 'featured_image_alt_review', 'Review media alt text', 'Featured image is present. Confirm the selected media item has meaningful alt text in the media library.', 'featured_image', 'Use descriptive alt text when the image adds editorial meaning.');
        $checklist[] = $this->check('featured_image', 'Featured image is selected', true, 'pass', 'Featured image is ready for preview and social use.');
    }

    /**
     * @param  array<int, array<string, bool>>  $images
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateImages(int $missingAltImageCount, array $images, array &$warnings, array &$suggestions, array &$checklist): void
    {
        if ($missingAltImageCount > 0) {
            $warnings[] = new SeoIssue('warning', 'inline_images_missing_alt', 'Inline images need alt text', sprintf('%d inline image(s) are missing alt text.', $missingAltImageCount), 'content', 'Add alt text to content images so they remain accessible and more descriptive to crawlers.');
        }

        if (count($images) >= 3 && $missingAltImageCount === count($images)) {
            $suggestions[] = new SeoIssue('suggestion', 'image_context_review', 'Balance images with context', 'The content uses several images, but they may need more surrounding text context.', 'content', 'Add supporting text around dense image sections where useful.');
        }

        $checklist[] = $this->check('inline_image_alt', 'Inline images include alt text', $missingAltImageCount === 0, $missingAltImageCount === 0 ? 'pass' : 'warning', $missingAltImageCount === 0 ? 'Inline image alt text looks healthy.' : 'Add alt text to the inline images flagged above.');
    }

    /**
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateLinks(int $wordCount, int $internalLinkCount, int $externalLinkCount, int $unsafeExternalLinks, array &$warnings, array &$suggestions, array &$checklist): void
    {
        if ($wordCount >= 180 && ($internalLinkCount + $externalLinkCount) === 0) {
            $warnings[] = new SeoIssue('warning', 'links_missing', 'Add relevant links', 'Long-form content does not include any internal or external links.', 'content', 'Link to related NewsTech content or credible external references where relevant.');
        }

        if ($unsafeExternalLinks > 0) {
            $warnings[] = new SeoIssue('warning', 'external_link_rel', 'External link safety needs review', 'Some external links are missing a safe rel attribute.', 'content', 'Use rel="noopener noreferrer" on external links opened in a new tab.');
        }

        if (($internalLinkCount + $externalLinkCount) > 20) {
            $suggestions[] = new SeoIssue('suggestion', 'links_dense', 'Review link density', 'The content contains a high number of links.', 'content', 'Confirm every link adds editorial value and does not distract from the main topic.');
        }

        $checklist[] = $this->check('links', 'Content uses helpful links', $wordCount < 180 || ($internalLinkCount + $externalLinkCount) > 0, ($wordCount < 180 || ($internalLinkCount + $externalLinkCount) > 0) ? 'pass' : 'warning', ($internalLinkCount + $externalLinkCount) > 0 ? 'Links are present.' : 'Add relevant internal or external links.');
    }

    /**
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateFocusKeyword(
        string $focusKeyword,
        string $title,
        string $slug,
        string $metaDescription,
        string $plainText,
        int $focusKeywordOccurrences,
        array &$warnings,
        array &$suggestions,
        array &$checklist,
    ): void {
        if ($focusKeyword === '') {
            $suggestions[] = new SeoIssue('suggestion', 'focus_keyword_missing', 'Set a focus keyword', 'No focus keyword is set for this draft.', 'focus_keyword', 'Add an optional focus keyword so the toolkit can check keyword placement.');
            $checklist[] = $this->check('focus_keyword', 'Focus keyword is set', false, 'suggestion', 'Add a focus keyword if you want keyword placement guidance.');

            return;
        }

        $normalizedKeyword = mb_strtolower($focusKeyword);
        $keywordChecks = [
            'title' => str_contains(mb_strtolower($title), $normalizedKeyword),
            'slug' => str_contains(mb_strtolower($slug), str_replace(' ', '-', $normalizedKeyword)),
            'meta_description' => str_contains(mb_strtolower($metaDescription), $normalizedKeyword),
            'content' => $focusKeywordOccurrences > 0,
        ];

        foreach ($keywordChecks as $field => $passed) {
            if (! $passed) {
                $warnings[] = new SeoIssue('warning', 'focus_keyword_'.$field, 'Use the focus keyword in more places', sprintf('The focus keyword is not present in the %s.', str_replace('_', ' ', $field)), $field === 'content' ? 'content' : $field, 'Work the focus keyword into this area naturally if it matches the editorial intent.');
            }
        }

        if ($focusKeywordOccurrences > 0 && $focusKeywordOccurrences < 2) {
            $suggestions[] = new SeoIssue('suggestion', 'focus_keyword_light', 'Use the focus keyword naturally', 'The focus keyword appears only once in the body content.', 'content', 'If it fits the editorial copy, mention the keyword one or two more times naturally.');
        }

        $checklist[] = $this->check('focus_keyword', 'Focus keyword appears across key fields', ! in_array(false, $keywordChecks, true), ! in_array(false, $keywordChecks, true) ? 'pass' : 'warning', ! in_array(false, $keywordChecks, true) ? 'Focus keyword placement looks healthy.' : 'Use the focus keyword in the missing areas where it fits naturally.');
    }

    /**
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateCanonical(string $canonicalUrl, array &$warnings, array &$suggestions, array &$checklist): void
    {
        if ($canonicalUrl === '') {
            $suggestions[] = new SeoIssue('suggestion', 'canonical_auto', 'Canonical URL will be generated automatically', 'No custom canonical URL is set, so the public route preview will be used.', 'canonical_url', 'Only set a custom canonical URL when you intentionally need a different canonical target.');
            $checklist[] = $this->check('canonical', 'Canonical URL resolves cleanly', true, 'pass', 'Automatic canonical preview is available.');

            return;
        }

        if (! filter_var($canonicalUrl, FILTER_VALIDATE_URL)) {
            $warnings[] = new SeoIssue('warning', 'canonical_invalid', 'Canonical URL is invalid', 'Canonical URL must be a valid absolute URL.', 'canonical_url', 'Use a full https:// URL for custom canonical values.');
            $checklist[] = $this->check('canonical', 'Canonical URL resolves cleanly', false, 'warning', 'Use a valid absolute canonical URL.');

            return;
        }

        $checklist[] = $this->check('canonical', 'Canonical URL resolves cleanly', true, 'pass', 'Custom canonical URL is valid.');
    }

    /**
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateStructuredDataReadiness(
        string $type,
        string $authorName,
        string $categoryName,
        string $publishedAt,
        int $tagCount,
        array &$warnings,
        array &$suggestions,
        array &$checklist,
    ): void {
        if ($type === 'article') {
            if ($authorName === '') {
                $warnings[] = new SeoIssue('warning', 'article_author_missing', 'Add an author', 'Article structured data is stronger when an author is assigned.', 'author_id', 'Assign an author for clearer NewsArticle schema and editorial transparency.');
            }

            if ($categoryName === '') {
                $warnings[] = new SeoIssue('warning', 'article_category_missing', 'Add a category', 'Article structured data is stronger when a category is assigned.', 'category_id', 'Assign a primary category to support topical organization.');
            }

            if ($publishedAt === '') {
                $suggestions[] = new SeoIssue('suggestion', 'article_publish_time_missing', 'Set a publish time when ready', 'Publish time is not set yet.', 'published_at', 'Set or confirm the publish time before going live for better structured data completeness.');
            }

            if ($tagCount === 0) {
                $suggestions[] = new SeoIssue('suggestion', 'article_tags_missing', 'Consider adding tags', 'No tags are selected for this article.', 'tag_ids', 'Add tags when they help topical discovery and related-linking.');
            }
        }

        $checklist[] = $this->check('structured_data', 'Structured data inputs are mostly complete', $type !== 'article' || ($authorName !== '' && $categoryName !== ''), ($type !== 'article' || ($authorName !== '' && $categoryName !== '')) ? 'pass' : 'warning', $type !== 'article' || ($authorName !== '' && $categoryName !== '') ? 'Schema inputs look healthy.' : 'Assign an author and category for better NewsArticle readiness.');
    }

    /**
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     */
    protected function evaluateVisibility(string $type, mixed $status, array &$suggestions, array &$checklist): void
    {
        $isPublished = match ($type) {
            'article' => $status === 'published',
            'page' => filter_var($status, FILTER_VALIDATE_BOOL),
            default => false,
        };

        if (! $isPublished) {
            $suggestions[] = new SeoIssue('suggestion', 'visibility_unpublished', 'Draft is not publicly visible yet', 'This entry is not published, so it may not appear in public SEO output such as frontend pages or sitemaps yet.', 'status', 'That is fine for drafts. Re-check the panel after switching to a public status.');
        }

        $checklist[] = $this->check('visibility', 'Entry is public and eligible for sitemap/feed exposure', $isPublished, $isPublished ? 'pass' : 'suggestion', $isPublished ? 'The entry is ready for public SEO exposure.' : 'The entry is still in a non-public state.');
    }

    protected function loadDom(string $html): ?DOMDocument
    {
        if ($html === '' || ! class_exists(DOMDocument::class)) {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="utf-8" ?><body>'.$html.'</body>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
        } catch (\Throwable) {
            libxml_use_internal_errors($previousState);
            libxml_clear_errors();

            return null;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        return $document;
    }

    protected function extractPlainText(string $html, ?DOMDocument $document): string
    {
        if (! $document) {
            return trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
        }

        foreach (iterator_to_array($document->getElementsByTagName('script')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        foreach (iterator_to_array($document->getElementsByTagName('style')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        $bodyText = $document->textContent ?? '';

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($bodyText, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    protected function countNodes(?DOMDocument $document, string $query): int
    {
        if (! $document) {
            return 0;
        }

        $xpath = new DOMXPath($document);

        return $xpath->query($query)?->length ?? 0;
    }

    protected function countEmptyParagraphs(?DOMDocument $document): int
    {
        if (! $document) {
            return 0;
        }

        $paragraphs = $document->getElementsByTagName('p');
        $count = 0;

        foreach ($paragraphs as $paragraph) {
            $text = trim($paragraph->textContent ?? '');
            $hasImage = $paragraph instanceof DOMElement && $paragraph->getElementsByTagName('img')->length > 0;

            if ($text === '' && ! $hasImage) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<int, array{missing_alt: bool}>
     */
    protected function extractImages(?DOMDocument $document): array
    {
        if (! $document) {
            return [];
        }

        $images = [];

        foreach ($document->getElementsByTagName('img') as $image) {
            $alt = trim((string) $image->getAttribute('alt'));

            $images[] = [
                'missing_alt' => $alt === '',
            ];
        }

        return $images;
    }

    /**
     * @return array<int, array{external: bool, unsafe_rel: bool}>
     */
    protected function extractLinks(?DOMDocument $document): array
    {
        if (! $document) {
            return [];
        }

        $links = [];

        foreach ($document->getElementsByTagName('a') as $link) {
            $href = trim((string) $link->getAttribute('href'));

            if ($href === '') {
                continue;
            }

            $external = Str::startsWith($href, ['http://', 'https://']);
            $rel = trim((string) $link->getAttribute('rel'));
            $unsafeRel = $external && ! Str::contains($rel, 'noopener');

            $links[] = [
                'external' => $external,
                'unsafe_rel' => $unsafeRel,
            ];
        }

        return $links;
    }

    /**
     * @return array<string, mixed>
     */
    protected function check(string $key, string $label, bool $passed, string $severity, string $message): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'passed' => $passed,
            'severity' => $severity,
            'message' => $message,
        ];
    }

    protected function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
