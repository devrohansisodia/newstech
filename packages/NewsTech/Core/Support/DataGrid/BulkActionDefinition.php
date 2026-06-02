<?php

namespace NewsTech\Core\Support\DataGrid;

class BulkActionDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public bool $isEnabled = false,
    ) {}

    public static function make(string $key, string $label): self
    {
        return new self($key, $label);
    }

    public function enabled(bool $isEnabled = true): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}
