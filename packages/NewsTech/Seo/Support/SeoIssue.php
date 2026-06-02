<?php

namespace NewsTech\Seo\Support;

class SeoIssue
{
    public function __construct(
        public string $severity,
        public string $code,
        public string $title,
        public string $message,
        public ?string $field = null,
        public ?string $recommendation = null,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'severity' => $this->severity,
            'code' => $this->code,
            'title' => $this->title,
            'message' => $this->message,
            'field' => $this->field,
            'recommendation' => $this->recommendation,
        ];
    }
}
