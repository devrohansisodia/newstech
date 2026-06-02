@include('newstech-admin::newsletter.campaigns._form', [
    'campaign' => $campaign,
    'action' => route('admin.newstech.newsletter.campaigns.store'),
    'method' => 'POST',
    'submitLabel' => 'Create Campaign',
    'pageTitle' => 'Create Newsletter Campaign',
    'pageDescription' => 'Create a draft or scheduled newsletter campaign that can later be sent to active subscribers.',
])
