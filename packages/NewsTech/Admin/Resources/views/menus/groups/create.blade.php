@include('newstech-admin::menus.groups._form', [
    'menuGroup' => $menuGroup,
    'locationOptions' => $locationOptions,
    'action' => route('admin.newstech.menus.store'),
    'submitLabel' => 'Create Menu Group',
    'pageTitle' => 'Create Menu Group',
    'pageDescription' => 'Create a new frontend navigation group for the header, footer, or a future mobile menu.',
])
