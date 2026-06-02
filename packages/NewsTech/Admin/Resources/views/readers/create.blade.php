@include('newstech-admin::readers._form', [
    'reader' => $reader,
    'action' => route('admin.newstech.readers.store'),
    'submitLabel' => 'Create Reader',
    'pageTitle' => 'Create Reader',
    'pageDescription' => 'Create a frontend reader account that stays completely separate from admin authentication.',
])
