@include('newstech-admin::newsletter.campaigns._form', [
    'campaign' => $campaign,
    'action' => route('admin.newstech.newsletter.campaigns.update', $campaign),
    'method' => 'PUT',
    'submitLabel' => 'Update Campaign',
    'pageTitle' => 'Edit Newsletter Campaign',
    'pageDescription' => 'Update this unsent newsletter campaign before delivery begins.',
])
