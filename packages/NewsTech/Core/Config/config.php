<?php

return [
    'name' => env('APP_NAME', 'NewsTech'),

    'brand' => [
        'name' => 'NewsTech',
        'tagline' => 'Modular publishing foundation for the modern newsroom.',
        'logo_path' => null,
        'footer_logo_path' => null,
    ],

    'meta' => [
        'default_title' => 'NewsTech',
        'default_description' => 'A modular Laravel news platform focused on editorial workflows, performance, and SEO.',
    ],

    'media' => [
        'disk' => 'public',
        'path' => 'newstech/media',
        'allowed_image_mime_types' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_upload_size' => 5120,
    ],

    'admin' => [
        'label' => 'NewsTech Admin',
    ],

    'homepage' => [
        'layout' => 'full_width',
        'sidebar_title' => null,
        'sidebar_content' => null,
        'sidebar_link_label' => null,
        'sidebar_link_url' => null,
    ],
];
