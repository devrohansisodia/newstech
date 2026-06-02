<?php

namespace NewsTech\Core\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlugHelper
{
    public static function generateSlug(string $value): string
    {
        return Str::slug($value);
    }

    public function fillSlugFrom(string $sourceAttribute, string $slugAttribute = 'slug'): static
    {
        $sourceValue = (string) $this->getAttribute($sourceAttribute);

        $this->setAttribute($slugAttribute, static::generateSlug($sourceValue));

        return $this;
    }
}
