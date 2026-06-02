<?php

return [
    [
        'key' => 'editorial.articles',
        'name' => 'Articles',
        'route' => 'admin.newstech.articles.index',
        'sort' => 1,
    ],
    [
        'key' => 'editorial.articles.view',
        'name' => 'View',
        'route' => 'admin.newstech.articles.index',
        'sort' => 1,
    ],
    [
        'key' => 'editorial.articles.create',
        'name' => 'Create',
        'route' => 'admin.newstech.articles.create',
        'sort' => 2,
    ],
    [
        'key' => 'editorial.articles.edit',
        'name' => 'Edit',
        'route' => 'admin.newstech.articles.edit',
        'sort' => 3,
    ],
    [
        'key' => 'editorial.articles.delete',
        'name' => 'Delete',
        'route' => 'admin.newstech.articles.destroy',
        'sort' => 4,
    ],
    [
        'key' => 'editorial.articles.publish',
        'name' => 'Publish',
        'route' => 'admin.newstech.articles.update',
        'sort' => 5,
    ],
];
