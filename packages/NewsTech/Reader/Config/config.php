<?php

return [
    'auth' => [
        'guard' => 'reader',
        'redirect_to' => 'newstech.account.dashboard',
        'login_route' => 'newstech.readers.login',
        'logout_route' => 'newstech.readers.logout',
        'password_broker' => 'readers',
    ],
];
