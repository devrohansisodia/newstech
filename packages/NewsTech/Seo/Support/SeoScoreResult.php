<?php

namespace NewsTech\Seo\Support;

class SeoScoreResult
{
    /**
     * @param  array<int, SeoIssue>  $errors
     * @param  array<int, SeoIssue>  $warnings
     * @param  array<int, SeoIssue>  $suggestions
     * @param  array<int, array<string, mixed>>  $checklist
     * @param  array<string, mixed>  $preview
     */
    public function __construct(
        public int $score,
        public string $grade,
        public array $errors,
        public array $warnings,
        public array $suggestions,
        public array $checklist,
        public array $preview,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'grade' => $this->grade,
            'errors' => array_map(fn (SeoIssue $issue): array => $issue->toArray(), $this->errors),
            'warnings' => array_map(fn (SeoIssue $issue): array => $issue->toArray(), $this->warnings),
            'suggestions' => array_map(fn (SeoIssue $issue): array => $issue->toArray(), $this->suggestions),
            'checklist' => $this->checklist,
            'preview' => $this->preview,
        ];
    }
}
