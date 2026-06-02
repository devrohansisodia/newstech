<?php

namespace NewsTech\Category\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Repositories\BaseRepository;

/**
 * @extends BaseRepository<Category>
 */
class CategoryRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    /**
     * @return Builder<Category>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->with('parent:id,name')
            ->ordered();
    }

    /**
     * @return array<int, string>
     */
    public function parentOptions(?int $excludeCategoryId = null): array
    {
        return $this->query()
            ->when($excludeCategoryId !== null, fn (Builder $query) => $query->whereKeyNot($excludeCategoryId))
            ->ordered()
            ->pluck('name', 'id')
            ->all();
    }

    public function nextSortOrder(): int
    {
        return Category::resolveNextSortOrder();
    }
}
