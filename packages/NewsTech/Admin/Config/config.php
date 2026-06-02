<?php

return [
    'auth' => [
        'email' => 'admin@newstech.test',
        'guard' => 'admin',
        'password_broker' => 'admins',
        'password' => 'password',
        'redirect_to' => 'admin.newstech.dashboard',
        'login_route' => 'admin.newstech.login',
        'logout_route' => 'admin.newstech.logout',
    ],

    'route' => [
        'middleware' => ['web'],
        'name' => 'admin.newstech.',
        'prefix' => 'admin',
    ],

    'navigation' => [
        'show_foundation_menu' => false,
    ],
];
