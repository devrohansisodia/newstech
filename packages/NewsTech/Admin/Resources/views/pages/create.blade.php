@include('newstech-admin::pages._form', [
    'page' => $page,
    'action' => route('admin.newstech.pages.store'),
    'submitLabel' => 'Create Page',
    'pageTitle' => 'Create Page',
    'pageDescription' => 'Create a new static page.',
])
