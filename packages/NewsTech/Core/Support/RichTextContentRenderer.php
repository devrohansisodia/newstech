<?php

namespace NewsTech\Core\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichTextContentRenderer
{
    /**
     * @var list<string>
     */
    protected array $blockedTags = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'form',
    ];

    public function render(?string $content): string
    {
        if (! filled($content)) {
            return '';
        }

        $previousState = libxml_use_internal_errors(true);

        $document = new DOMDocument('1.0', 'UTF-8');
        $wrappedHtml = '<?xml encoding="utf-8" ?><div>'.$content.'</div>';

        if (! $document->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);

            return '';
        }

        /** @var ?DOMElement $container */
        $container = $document->getElementsByTagName('div')->item(0);

        if (! $container) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);

            return '';
        }

        $this->sanitizeNode($container);

        $html = '';

        foreach (iterator_to_array($container->childNodes) as $childNode) {
            $html .= $document->saveHTML($childNode);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        return $html;
    }

    protected function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tagName = strtolower($node->tagName);

            if (in_array($tagName, $this->blockedTags, true)) {
                $node->parentNode?->removeChild($node);

                return;
            }

            if ($tagName === 'a') {
                $this->sanitizeAnchor($node);
            }

            if ($tagName === 'img') {
                if (! $this->sanitizeImage($node)) {
                    $node->parentNode?->removeChild($node);

                    return;
                }
            }

            if ($node->hasAttributes()) {
                foreach (iterator_to_array($node->attributes) as $attribute) {
                    $attributeName = strtolower($attribute->nodeName);

                    if (str_starts_with($attributeName, 'on')) {
                        $node->removeAttributeNode($attribute);

                        continue;
                    }

                    if (
                        in_array($attributeName, ['href', 'src'], true)
                        && $this->hasUnsafeProtocol($attribute->nodeValue)
                    ) {
                        $node->removeAttributeNode($attribute);
                    }
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $childNode) {
            $this->sanitizeNode($childNode);
        }
    }

    protected function sanitizeAnchor(DOMElement $anchor): void
    {
        $href = trim((string) $anchor->getAttribute('href'));

        if ($href === '' || $this->hasUnsafeProtocol($href)) {
            $anchor->removeAttribute('href');
            $anchor->removeAttribute('target');
            $anchor->removeAttribute('rel');

            return;
        }

        if ($this->isExternalHttpLink($href)) {
            $anchor->setAttribute('target', '_blank');
            $anchor->setAttribute('rel', 'noopener noreferrer');

            return;
        }

        $anchor->removeAttribute('target');
        $anchor->removeAttribute('rel');
    }

    protected function sanitizeImage(DOMElement $image): bool
    {
        $src = trim((string) $image->getAttribute('src'));

        if ($src === '' || $this->hasUnsafeProtocol($src)) {
            return false;
        }

        foreach (iterator_to_array($image->attributes) as $attribute) {
            $attributeName = strtolower($attribute->nodeName);

            if (in_array($attributeName, ['src', 'alt', 'title', 'width', 'height'], true)) {
                continue;
            }

            $image->removeAttributeNode($attribute);
        }

        foreach (['width', 'height'] as $dimensionAttribute) {
            $dimensionValue = trim((string) $image->getAttribute($dimensionAttribute));

            if ($dimensionValue === '') {
                $image->removeAttribute($dimensionAttribute);

                continue;
            }

            if (! ctype_digit($dimensionValue)) {
                $image->removeAttribute($dimensionAttribute);
            }
        }

        return true;
    }

    protected function hasUnsafeProtocol(string $value): bool
    {
        $normalizedValue = strtolower(trim($value));

        return str_starts_with($normalizedValue, 'javascript:')
            || str_starts_with($normalizedValue, 'data:')
            || str_starts_with($normalizedValue, 'vbscript:');
    }

    protected function isExternalHttpLink(string $href): bool
    {
        return str_starts_with($href, 'http://') || str_starts_with($href, 'https://');
    }
}
