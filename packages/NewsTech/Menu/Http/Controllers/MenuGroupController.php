<?php

namespace NewsTech\Menu\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;
use NewsTech\Menu\Http\Requests\StoreMenuGroupRequest;
use NewsTech\Menu\Http\Requests\UpdateMenuGroupRequest;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Repositories\MenuGroupRepository;
use NewsTech\Menu\Repositories\MenuItemRepository;

class MenuGroupController
{
    public function __construct(
        protected MenuGroupRepository $menuGroups,
        protected MenuItemRepository $menuItems
    ) {}

    public function index(): View
    {
        $menuGroups = $this->menuGroups->orderedQuery()->withCount('items')->get();

        $dataGrid = DataGridDefinition::make('menu-groups', 'Menu Groups')
            ->description('Manage the stored frontend navigation groups that can power header, footer, and future mobile menus.')
            ->columns([
                ColumnDefinition::make('name', 'Name')->sortable(),
                ColumnDefinition::make('location', 'Location'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('item_count', 'Items')->align('right'),
                ColumnDefinition::make('updated_at', 'Updated')->align('right')->sortable(),
            ])
            ->rows($menuGroups->map(fn (MenuGroup $group): array => [
                'id' => $group->getKey(),
                'name' => $group->name,
                'location' => $group->locationLabel(),
                'status_label' => $group->statusLabel(),
                'item_count' => (string) $group->items_count,
                'updated_at' => $group->updated_at?->format('M d, Y'),
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.menus.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.menus.destroy', $row['id'])),
            ])
            ->emptyState(
                'No menu groups yet.',
                'Create the first header, footer, or mobile menu group to start replacing hardcoded fallback navigation.'
            );

        return view('newstech-admin::menus.groups.index', [
            'dataGrid' => $dataGrid,
            'menuGroupCount' => $menuGroups->count(),
            'activeMenuGroupCount' => $menuGroups->where('status', true)->count(),
            'headerMenuGroupCount' => $menuGroups->where('location', 'header')->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::menus.groups.create', [
            'menuGroup' => new MenuGroup([
                'status' => true,
                'location' => 'header',
            ]),
            'locationOptions' => MenuGroup::locationOptions(),
        ]);
    }

    public function store(StoreMenuGroupRequest $request): RedirectResponse
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->create($request->validated());

        return redirect()
            ->route('admin.newstech.menus.edit', $menuGroup)
            ->with('menu_status', 'Menu group created successfully.');
    }

    public function edit(int|string $menu): View
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);
        $menuItems = $this->menuItems->orderedForGroupQuery($menuGroup)->get();

        $itemsGrid = DataGridDefinition::make('menu-items', 'Menu Items')
            ->description('Menu items stay simple in this foundation phase: sorting is numeric, and item editing is handled through standard Blade forms.')
            ->columns([
                ColumnDefinition::make('label', 'Label'),
                ColumnDefinition::make('type', 'Type'),
                ColumnDefinition::make('target', 'Target'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('sort_order', 'Order')->align('right'),
            ])
            ->rows($menuItems->map(fn ($item): array => [
                'id' => $item->getKey(),
                'label' => $item->label,
                'type' => $item->typeLabel(),
                'target' => $item->opens_in_new_tab ? 'New tab' : 'Same tab',
                'status_label' => $item->statusLabel(),
                'sort_order' => (string) $item->sort_order,
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.menus.items.edit', [$menuGroup, $row['id']])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.menus.items.destroy', [$menuGroup, $row['id']])),
            ])
            ->emptyState(
                'No menu items yet.',
                'Add the first menu item for this group using a custom URL, page, or category target.'
            );

        return view('newstech-admin::menus.groups.edit', [
            'menuGroup' => $menuGroup,
            'locationOptions' => MenuGroup::locationOptions(),
            'itemsGrid' => $itemsGrid,
        ]);
    }

    public function update(UpdateMenuGroupRequest $request, int|string $menu): RedirectResponse
    {
        /** @var MenuGroup $menuGroup */
        $menuGroup = $this->menuGroups->findOrFail($menu);

        $this->menuGroups->update($menuGroup, $request->validated());

        return redirect()
            ->route('admin.newstech.menus.edit', $menuGroup)
            ->with('menu_status', 'Menu group updated successfully.');
    }

    public function destroy(int|string $menu): RedirectResponse
    {
        $this->menuGroups->delete($menu);

        return redirect()
            ->route('admin.newstech.menus.index')
            ->with('menu_status', 'Menu group deleted successfully.');
    }
}
