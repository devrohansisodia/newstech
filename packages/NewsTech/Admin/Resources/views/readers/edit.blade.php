@include('newstech-admin::readers._form', [
    'reader' => $reader,
    'action' => route('admin.newstech.readers.update', $reader),
    'method' => 'PUT',
    'submitLabel' => 'Update Reader',
    'pageTitle' => 'Edit Reader',
    'pageDescription' => 'Update an existing frontend reader account, including status and optional password replacement.',
])
