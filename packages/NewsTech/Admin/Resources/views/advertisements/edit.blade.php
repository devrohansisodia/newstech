@include('newstech-admin::advertisements._form', [
    'advertisement' => $advertisement,
    'action' => route('admin.newstech.advertisements.update', $advertisement),
    'method' => 'PUT',
    'slotOptions' => $slotOptions,
    'submitLabel' => 'Update Advertisement',
    'pageTitle' => 'Edit Advertisement',
    'pageDescription' => 'Update advertisement content, slot targeting, schedule, and tracking behavior without touching frontend blades.',
])
