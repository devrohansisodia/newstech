<?php

namespace NewsTech\Newsletter\Support;

use Illuminate\Http\Request;
use NewsTech\Core\Support\SystemSettingsManager;

class PersistNewsletterSettings
{
    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $group
     */
    public function __invoke(
        Request $request,
        array $validated,
        array $group,
        SystemSettingsManager $settingsManager,
    ): void {
        $settingsManager->setMany([
            'newsletter.enabled' => $request->boolean('enabled'),
            'newsletter.double_opt_in' => $request->boolean('double_opt_in'),
            'newsletter.allow_resubscribe' => $request->boolean('allow_resubscribe'),
            'newsletter.sender_name' => $validated['sender_name'] ?: null,
            'newsletter.sender_email' => $validated['sender_email'] ?: null,
            'newsletter.footer_unsubscribe_text' => $validated['footer_unsubscribe_text'] ?: null,
        ]);
    }
}
