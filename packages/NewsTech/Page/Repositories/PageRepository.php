<?php

namespace NewsTech\Page\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Page\Models\Page;

/**
 * @extends BaseRepository<Page>
 */
class PageRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Page::class;
    }

    /**
     * @return Builder<Page>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()->latest('updated_at');
    }

    /**
     * @return Builder<Page>
     */
    public function activeQuery(): Builder
    {
        return $this->query()
            ->where('status', true)
            ->latest('updated_at');
    }

    public function findActiveBySlug(string $slug): ?Page
    {
        /** @var ?Page $page */
        $page = $this->activeQuery()
            ->where('slug', $slug)
            ->first();

        return $page;
    }
}
