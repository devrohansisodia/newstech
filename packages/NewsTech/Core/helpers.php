<?php

use Illuminate\Support\HtmlString;
use NewsTech\Core\Support\RenderEventManager;

if (! function_exists('newstech_view_render_event')) {
    function newstech_view_render_event(string $event, array $data = []): HtmlString
    {
        return app(RenderEventManager::class)->render($event, $data);
    }
}
