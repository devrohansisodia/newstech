<?php

return [
    [
        'key' => 'editorial.comments',
        'name' => 'Comments',
        'route' => 'admin.newstech.comments.index',
        'sort' => 2,
    ],
    [
        'key' => 'editorial.comments.view',
        'name' => 'View',
        'route' => 'admin.newstech.comments.index',
        'sort' => 1,
    ],
    [
        'key' => 'editorial.comments.approve',
        'name' => 'Approve',
        'route' => 'admin.newstech.comments.approve',
        'sort' => 2,
    ],
    [
        'key' => 'editorial.comments.reject',
        'name' => 'Reject',
        'route' => 'admin.newstech.comments.reject',
        'sort' => 3,
    ],
    [
        'key' => 'editorial.comments.delete',
        'name' => 'Delete',
        'route' => 'admin.newstech.comments.destroy',
        'sort' => 4,
    ],
];
