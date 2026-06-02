@include('newstech-admin::tags._form', [
    'tag' => $tag,
    'action' => route('admin.newstech.tags.store'),
    'submitLabel' => 'Create Tag',
    'pageTitle' => 'Create Tag',
    'pageDescription' => 'Create a reusable topic tag.',
])
