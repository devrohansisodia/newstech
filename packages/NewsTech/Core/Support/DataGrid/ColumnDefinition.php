<?php

namespace NewsTech\Core\Support\DataGrid;

class ColumnDefinition
{
    /**
     * @param  array<string, string>  $toneMap
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'text',
        public string $alignment = 'left',
        public bool $isSearchable = false,
        public bool $isSortable = false,
        public ?string $toneKey = null,
        public array $toneMap = [],
    ) {}

    public static function make(string $key, string $label): self
    {
        return new self($key, $label);
    }

    public function align(string $alignment): self
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function searchable(bool $isSearchable = true): self
    {
        $this->isSearchable = $isSearchable;

        return $this;
    }

    public function sortable(bool $isSortable = true): self
    {
        $this->isSortable = $isSortable;

        return $this;
    }

    /**
     * @param  array<string, string>  $toneMap
     */
    public function badge(?string $toneKey = null, array $toneMap = []): self
    {
        $this->type = 'badge';
        $this->toneKey = $toneKey;
        $this->toneMap = $toneMap;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function resolveValue(array $row): mixed
    {
        return data_get($row, $this->key);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function resolveTone(array $row): string
    {
        if ($this->type !== 'badge') {
            return 'neutral';
        }

        $toneKey = $this->toneKey ?? $this->key;
        $tone = data_get($row, $toneKey);

        if (! is_string($tone)) {
            return 'neutral';
        }

        return $this->toneMap[$tone] ?? 'neutral';
    }
}
