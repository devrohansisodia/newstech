<?php

namespace NewsTech\Core\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class MenuTreeBuilder
{
    /**
     * Build a nested menu tree from a flat configuration array.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function build(array $items): array
    {
        $indexedItems = [];

        foreach ($items as $item) {
            $key = (string) Arr::get($item, 'key');

            $indexedItems[$key] = [
                'key' => $key,
                'name' => (string) Arr::get($item, 'name', $key),
                'route' => Arr::get($item, 'route'),
                'sort' => (int) Arr::get($item, 'sort', 0),
                'icon' => (string) Arr::get($item, 'icon', ''),
                'children' => [],
                'is_active' => false,
                'url' => $this->resolveRouteUrl(Arr::get($item, 'route')),
            ];
        }

        $tree = [];

        foreach ($indexedItems as $key => &$item) {
            $parentKey = $this->resolveParentKey($key);

            $item['is_active'] = $this->isActiveRoute($item['route'], $key);

            if ($parentKey === null || ! isset($indexedItems[$parentKey])) {
                $tree[] = &$item;

                continue;
            }

            $indexedItems[$parentKey]['children'][] = &$item;
        }

        unset($item);

        return $this->sortTree($tree);
    }

    /**
     * Count all nodes in a nested tree.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function count(array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $count++;
            $count += $this->count($item['children']);
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function sortTree(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            return [$left['sort'], $left['name']] <=> [$right['sort'], $right['name']];
        });

        foreach ($items as &$item) {
            $item['children'] = $this->sortTree($item['children']);
            $item['is_active'] = $item['is_active'] || collect($item['children'])->contains('is_active', true);
        }

        unset($item);

        return $items;
    }

    protected function resolveParentKey(string $key): ?string
    {
        if (! str_contains($key, '.')) {
            return null;
        }

        return (string) str($key)->beforeLast('.');
    }

    protected function resolveRouteUrl(mixed $route): ?string
    {
        if (! is_string($route) || ! Route::has($route)) {
            return null;
        }

        return route($route);
    }

    protected function isActiveRoute(mixed $route, string $key): bool
    {
        $currentRouteName = request()->route()?->getName();

        if (! is_string($currentRouteName)) {
            return false;
        }

        if (is_string($route) && $currentRouteName === $route) {
            return true;
        }

        return str_starts_with($currentRouteName, $key.'.');
    }
}
