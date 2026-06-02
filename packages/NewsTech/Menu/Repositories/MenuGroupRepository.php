<?php

namespace NewsTech\Menu\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Menu\Models\MenuGroup;

/**
 * @extends BaseRepository<MenuGroup>
 */
class MenuGroupRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return MenuGroup::class;
    }

    /**
     * @return Builder<MenuGroup>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()->latest('updated_at');
    }

    /**
     * @return Builder<MenuGroup>
     */
    public function activeQuery(): Builder
    {
        return $this->query()
            ->where('status', true)
            ->latest('updated_at');
    }

    public function activeByLocation(string $location): ?MenuGroup
    {
        /** @var ?MenuGroup $group */
        $group = $this->activeQuery()
            ->where('location', $location)
            ->with([
                'items' => fn ($query) => $query->with(['page:id,title,slug,status', 'category:id,name,slug,status']),
                'items.children' => fn ($query) => $query->with(['page:id,title,slug,status', 'category:id,name,slug,status']),
            ])
            ->first();

        return $group;
    }
}
