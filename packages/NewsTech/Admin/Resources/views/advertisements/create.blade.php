@include('newstech-admin::advertisements._form', [
    'advertisement' => $advertisement,
    'action' => route('admin.newstech.advertisements.store'),
    'slotOptions' => $slotOptions,
    'submitLabel' => 'Create Advertisement',
    'pageTitle' => 'Create Advertisement',
    'pageDescription' => 'Create a managed advertisement that can render into an existing NewsTech render-event slot.',
])
