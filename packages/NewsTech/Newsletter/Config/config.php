<?php

return [
    'enabled' => true,
    'double_opt_in' => false,
    'allow_resubscribe' => true,
    'sender_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'NewsTech')),
    'sender_email' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'footer_unsubscribe_text' => 'You are receiving this email because you subscribed to newsletter updates. Use the unsubscribe link below to stop future campaign emails.',
];
