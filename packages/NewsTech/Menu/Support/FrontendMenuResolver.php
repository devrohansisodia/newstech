<?php

namespace NewsTech\Menu\Support;

use Illuminate\Support\Collection;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;
use NewsTech\Menu\Repositories\MenuGroupRepository;

class FrontendMenuResolver
{
    public function __construct(protected MenuGroupRepository $menuGroups) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function itemsForLocation(string $location): Collection
    {
        $group = $this->menuGroups->activeByLocation($location);

        if (! $group) {
            return collect();
        }

        return $this->resolveGroupItems($group);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function resolveGroupItems(MenuGroup $group): Collection
    {
        /** @var Collection<int, MenuItem> $items */
        $items = $group->items->where('status', true)->values();

        return $items
            ->whereNull('parent_id')
            ->map(fn (MenuItem $item): ?array => $this->mapItem($item, $items))
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, MenuItem>  $groupItems
     * @return array<string, mixed>|null
     */
    protected function mapItem(MenuItem $item, Collection $groupItems): ?array
    {
        $url = $this->resolveItemUrl($item);

        if ($url === null) {
            return null;
        }

        $children = $groupItems
            ->where('parent_id', $item->getKey())
            ->map(fn (MenuItem $child): ?array => $this->mapItem($child, $groupItems))
            ->filter()
            ->values();

        return [
            'id' => $item->getKey(),
            'label' => $item->label,
            'url' => $url,
            'target' => $item->opens_in_new_tab ? '_blank' : '_self',
            'children' => $children,
        ];
    }

    protected function resolveItemUrl(MenuItem $item): ?string
    {
        return match ($item->type) {
            'custom_url' => filled($item->url) ? $item->url : null,
            'page' => $this->resolvePageUrl($item),
            'category' => $this->resolveCategoryUrl($item),
            default => null,
        };
    }

    protected function resolvePageUrl(MenuItem $item): ?string
    {
        $page = $item->page;

        if (! $page || ! $page->status) {
            return null;
        }

        $staticRouteMap = [
            'about' => 'newstech.about',
            'contact' => 'newstech.contact',
            'privacy-policy' => 'newstech.privacy-policy',
            'terms' => 'newstech.terms',
        ];

        if (isset($staticRouteMap[$page->slug])) {
            return route($staticRouteMap[$page->slug]);
        }

        return route('newstech.pages.show', ['slug' => $page->slug]);
    }

    protected function resolveCategoryUrl(MenuItem $item): ?string
    {
        $category = $item->category;

        if (! $category || ! $category->status) {
            return null;
        }

        return route('newstech.categories.show', ['slug' => $category->slug]);
    }
}
