<?php

namespace NewsTech\Menu\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;

/**
 * @extends BaseRepository<MenuItem>
 */
class MenuItemRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return MenuItem::class;
    }

    /**
     * @return Builder<MenuItem>
     */
    public function orderedForGroupQuery(MenuGroup|int $group): Builder
    {
        $groupId = $group instanceof MenuGroup ? $group->getKey() : $group;

        return $this->query()
            ->where('menu_group_id', $groupId)
            ->with(['parent:id,label', 'page:id,title', 'category:id,name'])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public function parentOptions(MenuGroup $group, ?int $excludeItemId = null): array
    {
        return $this->orderedForGroupQuery($group)
            ->when($excludeItemId !== null, fn (Builder $query) => $query->whereKeyNot($excludeItemId))
            ->pluck('label', 'id')
            ->all();
    }
}
