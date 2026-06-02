<?php

namespace NewsTech\Menu\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Category\Models\Category;
use NewsTech\Menu\Http\Requests\StoreMenuItemRequest;
use NewsTech\Menu\Http\Requests\UpdateMenuItemRequest;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;
use NewsTech\Menu\Repositories\MenuGroupRepository;
use NewsTech\Menu\Repositories\MenuItemRepository;
use NewsTech\Page\Models\Page;

class MenuItemController
{
    public function __construct(
        protected MenuGroupRepository $menuGroups,
        protected MenuItemRepository $menuItems
    ) {}

    public function create(int|string $menu): View
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);

        return view('newstech-admin::menus.items.create', [
            'menuGroup' => $menuGroup,
            'menuItem' => new MenuItem([
                'status' => true,
                'sort_order' => 0,
            ]),
            ...$this->formData($menuGroup),
        ]);
    }

    public function store(StoreMenuItemRequest $request, int|string $menu): RedirectResponse
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);

        $this->menuItems->create([
            ...$request->validated(),
            'menu_group_id' => $menuGroup->getKey(),
        ]);

        return redirect()
            ->route('admin.newstech.menus.edit', $menuGroup)
            ->with('menu_status', 'Menu item created successfully.');
    }

    public function edit(int|string $menu, int|string $item): View
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);
        /** @var MenuItem $menuItem */
        $menuItem = $this->menuItems->findOrFail($item);

        abort_unless($menuItem->menu_group_id === $menuGroup->getKey(), 404);

        return view('newstech-admin::menus.items.edit', [
            'menuGroup' => $menuGroup,
            'menuItem' => $menuItem,
            ...$this->formData($menuGroup, $menuItem),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, int|string $menu, int|string $item): RedirectResponse
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);
        /** @var MenuItem $menuItem */
        $menuItem = $this->menuItems->findOrFail($item);

        abort_unless($menuItem->menu_group_id === $menuGroup->getKey(), 404);

        $this->menuItems->update($menuItem, $request->validated());

        return redirect()
            ->route('admin.newstech.menus.edit', $menuGroup)
            ->with('menu_status', 'Menu item updated successfully.');
    }

    public function destroy(int|string $menu, int|string $item): RedirectResponse
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);
        /** @var MenuItem $menuItem */
        $menuItem = $this->menuItems->findOrFail($item);

        abort_unless($menuItem->menu_group_id === $menuGroup->getKey(), 404);

        $this->menuItems->delete($menuItem);

        return redirect()
            ->route('admin.newstech.menus.edit', $menuGroup)
            ->with('menu_status', 'Menu item deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(MenuGroup $menuGroup, ?MenuItem $menuItem = null): array
    {
        return [
            'typeOptions' => MenuItem::typeOptions(),
            'parentOptions' => $this->menuItems->parentOptions($menuGroup, $menuItem?->getKey()),
            'pageOptions' => Page::query()
                ->orderBy('title')
                ->pluck('title', 'id')
                ->all(),
            'categoryOptions' => Category::query()
                ->ordered()
                ->pluck('name', 'id')
                ->all(),
        ];
    }
}
