<?php

namespace NewsTech\Core\Support\DataGrid;

use Closure;

class ActionDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public string $method = 'GET',
        public string $tone = 'neutral',
        public string|Closure|null $url = null,
    ) {}

    public static function make(string $key, string $label): self
    {
        return new self($key, $label);
    }

    public function tone(string $tone): self
    {
        $this->tone = $tone;

        return $this;
    }

    public function usingMethod(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function url(string|Closure|null $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function usesFormSubmission(): bool
    {
        return $this->method !== 'GET';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function resolveUrl(array $row): ?string
    {
        if ($this->url instanceof Closure) {
            $resolvedUrl = ($this->url)($row);

            return is_string($resolvedUrl) ? $resolvedUrl : null;
        }

        return $this->url;
    }
}
