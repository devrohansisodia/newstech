@include('newstech-admin::menus.items._form', [
    'menuGroup' => $menuGroup,
    'menuItem' => $menuItem,
    'typeOptions' => $typeOptions,
    'parentOptions' => $parentOptions,
    'pageOptions' => $pageOptions,
    'categoryOptions' => $categoryOptions,
    'action' => route('admin.newstech.menus.items.update', [$menuGroup, $menuItem]),
    'method' => 'PUT',
    'submitLabel' => 'Update Menu Item',
    'pageTitle' => 'Edit Menu Item',
    'pageDescription' => 'Update the label, target, status, and ordering for an existing stored menu item.',
])
