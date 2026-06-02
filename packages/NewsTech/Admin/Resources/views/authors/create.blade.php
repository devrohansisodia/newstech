@include('newstech-admin::authors._form', [
    'author' => $author,
    'action' => route('admin.newstech.authors.store'),
    'submitLabel' => 'Create Author',
    'pageTitle' => 'Create Author',
    'pageDescription' => 'Create a reporter or contributor profile.',
])
