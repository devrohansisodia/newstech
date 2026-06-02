<?php

namespace NewsTech\Admin\Support;

class AdminMenuResolver
{
    /**
     * Filter menu items against available ACL keys.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $aclTree
     * @return array<int, array<string, mixed>>
     */
    public function filterByAcl(array $items, array $aclTree): array
    {
        $availableAclKeys = $this->flattenAclKeys($aclTree);

        return array_values(array_filter(
            array_map(
                fn (array $item): ?array => $this->filterItem($item, $availableAclKeys),
                $items
            )
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $aclTree
     * @return array<int, string>
     */
    protected function flattenAclKeys(array $aclTree): array
    {
        $keys = [];

        foreach ($aclTree as $item) {
            $keys[] = $item['key'];
            $keys = [...$keys, ...$this->flattenAclKeys($item['children'])];
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $availableAclKeys
     * @return array<string, mixed>|null
     */
    protected function filterItem(array $item, array $availableAclKeys): ?array
    {
        if ($this->shouldHideItem($item)) {
            return null;
        }

        $children = array_values(array_filter(
            array_map(
                fn (array $child): ?array => $this->filterItem($child, $availableAclKeys),
                $item['children']
            )
        ));

        $item['children'] = $children;

        if (in_array($item['key'], $availableAclKeys, true) || $children !== []) {
            return $item;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function shouldHideItem(array $item): bool
    {
        if (
            ($item['key'] ?? null) === 'site.advertisements'
            && ! config('newstech-advertisement.enabled', true)
        ) {
            return true;
        }

        if (! config('newstech-admin.navigation.show_foundation_menu', false)) {
            return str($item['key'])->startsWith('foundation');
        }

        return false;
    }
}
