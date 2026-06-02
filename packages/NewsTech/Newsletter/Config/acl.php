<?php

return [
    [
        'key' => 'site.newsletter',
        'name' => 'Newsletter',
        'route' => 'admin.newstech.newsletter.index',
        'sort' => 4,
    ],
    [
        'key' => 'site.newsletter_subscribers.view',
        'name' => 'Subscribers',
        'route' => 'admin.newstech.newsletter.index',
        'sort' => 1,
    ],
    [
        'key' => 'site.newsletter_subscribers.edit',
        'name' => 'Subscriber Updates',
        'route' => 'admin.newstech.newsletter.subscribers.update',
        'sort' => 2,
    ],
    [
        'key' => 'site.newsletter_subscribers.delete',
        'name' => 'Subscriber Delete',
        'route' => 'admin.newstech.newsletter.subscribers.destroy',
        'sort' => 3,
    ],
    [
        'key' => 'site.newsletter_campaigns',
        'name' => 'Campaigns',
        'route' => 'admin.newstech.newsletter.campaigns.index',
        'sort' => 4,
    ],
    [
        'key' => 'site.newsletter_campaigns.view',
        'name' => 'Campaign View',
        'route' => 'admin.newstech.newsletter.campaigns.index',
        'sort' => 5,
    ],
    [
        'key' => 'site.newsletter_campaigns.create',
        'name' => 'Campaign Create',
        'route' => 'admin.newstech.newsletter.campaigns.store',
        'sort' => 6,
    ],
    [
        'key' => 'site.newsletter_campaigns.edit',
        'name' => 'Campaign Edit',
        'route' => 'admin.newstech.newsletter.campaigns.update',
        'sort' => 7,
    ],
    [
        'key' => 'site.newsletter_campaigns.delete',
        'name' => 'Campaign Delete',
        'route' => 'admin.newstech.newsletter.campaigns.destroy',
        'sort' => 8,
    ],
    [
        'key' => 'site.newsletter_campaigns.send',
        'name' => 'Campaign Send',
        'route' => 'admin.newstech.newsletter.campaigns.send',
        'sort' => 9,
    ],
];
