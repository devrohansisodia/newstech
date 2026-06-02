<?php

namespace NewsTech\Advertisement\Support;

use Illuminate\Http\Request;
use NewsTech\Core\Support\SystemSettingsManager;

class PersistAdvertisementSettings
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(Request $request, array $validated, SystemSettingsManager $systemSettingsManager): void
    {
        $systemSettingsManager->setMany([
            'advertisements.enabled' => $request->boolean('advertisements_enabled'),
            'advertisements.placeholders_enabled' => $request->boolean('placeholders_enabled'),
            'advertisements.track_impressions' => $request->boolean('track_impressions'),
            'advertisements.track_clicks' => $request->boolean('track_clicks'),
            'advertisements.default_open_in_new_tab' => $request->boolean('default_open_in_new_tab'),
            'advertisements.default_nofollow' => $request->boolean('default_nofollow'),
            'advertisements.default_sponsored' => $request->boolean('default_sponsored'),
        ]);
    }
}
