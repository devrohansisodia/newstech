@include('newstech-admin::menus.items._form', [
    'menuGroup' => $menuGroup,
    'menuItem' => $menuItem,
    'typeOptions' => $typeOptions,
    'parentOptions' => $parentOptions,
    'pageOptions' => $pageOptions,
    'categoryOptions' => $categoryOptions,
    'action' => route('admin.newstech.menus.items.store', $menuGroup),
    'submitLabel' => 'Create Menu Item',
    'pageTitle' => 'Create Menu Item',
    'pageDescription' => 'Add a stored frontend navigation item for the selected menu group.',
])
