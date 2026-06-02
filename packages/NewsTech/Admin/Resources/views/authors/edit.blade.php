@include('newstech-admin::authors._form', [
    'author' => $author,
    'action' => route('admin.newstech.authors.update', $author),
    'method' => 'PUT',
    'submitLabel' => 'Update Author',
    'pageTitle' => 'Edit Author',
    'pageDescription' => 'Update profile details, avatar, and metadata.',
])
