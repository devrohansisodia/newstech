@include('newstech-admin::pages._form', [
    'page' => $page,
    'action' => route('admin.newstech.pages.update', $page),
    'method' => 'PUT',
    'submitLabel' => 'Update Page',
    'pageTitle' => 'Edit Page',
    'pageDescription' => 'Update page content and publishing details.',
])
