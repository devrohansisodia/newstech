<?php

namespace NewsTech\Admin\Support;

use Illuminate\Support\Arr;

class SystemConfigResolver
{
    /**
     * Build a nested settings tree from flat configuration items.
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
                'info' => (string) Arr::get($item, 'info', ''),
                'icon' => (string) Arr::get($item, 'icon', ''),
                'sort' => (int) Arr::get($item, 'sort', 0),
                'fields' => Arr::get($item, 'fields', []),
                'children' => [],
            ];
        }

        $tree = [];

        foreach ($indexedItems as $key => &$item) {
            $parentKey = $this->resolveParentKey($key);

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
     * Count all nodes in the settings tree.
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
}
