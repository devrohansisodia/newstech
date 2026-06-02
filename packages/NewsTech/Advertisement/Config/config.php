<?php

return [
    'enabled' => true,

    'placeholders_enabled' => false,

    'track_impressions' => true,

    'track_clicks' => true,

    'default_open_in_new_tab' => true,

    'default_nofollow' => false,

    'default_sponsored' => true,

    'slots' => [
        'header_leaderboard' => [
            'key' => 'header_leaderboard',
            'enabled' => true,
            'label' => 'Header Leaderboard',
            'description' => 'Reserved for a future top-of-page leaderboard advertisement slot.',
            'suggested_size' => '728x90 or responsive leaderboard',
            'min_height' => 'min-h-[5.5rem]',
            'render_events' => [
                ['key' => 'frontend.layout.header.after', 'compact' => true],
            ],
        ],
        'homepage_top' => [
            'key' => 'homepage_top',
            'enabled' => true,
            'label' => 'Homepage Top',
            'description' => 'Homepage promotional slot above the main editorial feed.',
            'suggested_size' => '970x250 or responsive hero banner',
            'min_height' => 'min-h-[7rem]',
            'render_events' => [
                ['key' => 'frontend.homepage.top.after', 'compact' => false],
            ],
        ],
        'homepage_sidebar' => [
            'key' => 'homepage_sidebar',
            'enabled' => true,
            'label' => 'Homepage Sidebar',
            'description' => 'Right-rail placeholder for homepage sponsorship or editorial promos.',
            'suggested_size' => '300x600 or stacked sidebar card',
            'min_height' => 'min-h-[16rem]',
            'render_events' => [
                ['key' => 'frontend.homepage.sidebar.top', 'compact' => false],
                ['key' => 'frontend.homepage.sidebar.inline', 'compact' => false],
            ],
        ],
        'article_top' => [
            'key' => 'article_top',
            'enabled' => true,
            'label' => 'Article Top',
            'description' => 'Reserved for a future hero-ad placement above article content.',
            'suggested_size' => '728x90 or responsive inline banner',
            'min_height' => 'min-h-[6rem]',
            'render_events' => [
                ['key' => 'frontend.article.show.top.before', 'compact' => true],
            ],
        ],
        'article_inline' => [
            'key' => 'article_inline',
            'enabled' => true,
            'label' => 'Article Inline',
            'description' => 'Inline article placeholder between story body sections.',
            'suggested_size' => '728x90 or in-content block',
            'min_height' => 'min-h-[9rem]',
            'render_events' => [
                ['key' => 'frontend.article.show.content.after', 'compact' => true],
            ],
        ],
        'article_sidebar' => [
            'key' => 'article_sidebar',
            'enabled' => true,
            'label' => 'Article Sidebar',
            'description' => 'Sidebar ad slot for article detail pages.',
            'suggested_size' => '300x250 or 300x600',
            'min_height' => 'min-h-[14rem]',
            'render_events' => [
                ['key' => 'frontend.article.show.sidebar.top', 'compact' => true],
            ],
        ],
        'listing_top' => [
            'key' => 'listing_top',
            'enabled' => true,
            'label' => 'Listing Top',
            'description' => 'Top slot for taxonomy and search result listing pages.',
            'suggested_size' => '728x90 responsive listing banner',
            'min_height' => 'min-h-[6rem]',
            'render_events' => [
                ['key' => 'frontend.listing.top', 'compact' => true],
            ],
        ],
        'footer_banner' => [
            'key' => 'footer_banner',
            'enabled' => true,
            'label' => 'Footer Banner',
            'description' => 'Footer placeholder for future sitewide campaigns.',
            'suggested_size' => '728x90 footer banner',
            'min_height' => 'min-h-[5.5rem]',
            'render_events' => [
                ['key' => 'frontend.layout.footer.after', 'compact' => true],
            ],
        ],
    ],
];
