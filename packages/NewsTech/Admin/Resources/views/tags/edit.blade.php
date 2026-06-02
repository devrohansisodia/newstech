@include('newstech-admin::tags._form', [
    'tag' => $tag,
    'action' => route('admin.newstech.tags.update', $tag),
    'method' => 'PUT',
    'submitLabel' => 'Update Tag',
    'pageTitle' => 'Edit Tag',
    'pageDescription' => 'Update tag details and metadata.',
])
