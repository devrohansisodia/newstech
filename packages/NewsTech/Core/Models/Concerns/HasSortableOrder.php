<?php

namespace NewsTech\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasSortableOrder
{
    public function scopeOrdered(Builder $query, string $column = 'sort_order', string $direction = 'asc'): Builder
    {
        return $query->orderBy($column, $direction);
    }

    public static function resolveNextSortOrder(?Builder $query = null, string $column = 'sort_order'): int
    {
        $query ??= static::query();

        return (int) $query->max($column) + 1;
    }
}
