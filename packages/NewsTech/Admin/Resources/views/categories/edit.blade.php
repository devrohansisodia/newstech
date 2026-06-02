@include('newstech-admin::categories._form', [
    'category' => $category,
    'parentOptions' => $parentOptions,
    'action' => route('admin.newstech.categories.update', $category),
    'method' => 'PUT',
    'submitLabel' => 'Update Category',
    'pageTitle' => 'Edit Category',
    'pageDescription' => 'Update hierarchy, visibility, and metadata.',
])
