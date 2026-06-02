@include('newstech-admin::categories._form', [
    'category' => $category,
    'parentOptions' => $parentOptions,
    'action' => route('admin.newstech.categories.store'),
    'submitLabel' => 'Create Category',
    'pageTitle' => 'Create Category',
    'pageDescription' => 'Create a category for article organization.',
])
