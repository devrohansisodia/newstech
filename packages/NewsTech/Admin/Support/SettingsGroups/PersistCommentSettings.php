<?php

namespace NewsTech\Admin\Support\SettingsGroups;

use Illuminate\Http\Request;
use NewsTech\Core\Support\SystemSettingsManager;

class PersistCommentSettings
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(Request $request, array $validated, SystemSettingsManager $systemSettingsManager): void
    {
        $systemSettingsManager->setMany([
            'comments.enabled' => $request->boolean('comments_enabled'),
            'comments.require_moderation' => $request->boolean('require_moderation'),
            'comments.guest_comments_enabled' => $request->boolean('guest_comments_enabled'),
            'comments.website_field_enabled' => $request->boolean('website_field_enabled'),
            'comments.min_comment_length' => (int) $validated['min_comment_length'],
            'comments.max_comment_length' => (int) $validated['max_comment_length'],
            'comments.max_links_per_comment' => (int) $validated['max_links_per_comment'],
            'comments.blocked_words' => $this->normalizedMultilineSetting($validated['blocked_words'] ?? null),
            'comments.blocked_emails' => $this->normalizedMultilineSetting($validated['blocked_emails'] ?? null),
            'comments.blocked_ips' => $this->normalizedMultilineSetting($validated['blocked_ips'] ?? null),
            'comments.auto_reject_spam' => $request->boolean('auto_reject_spam'),
            'comments.throttle_seconds' => (int) $validated['throttle_seconds'],
        ]);
    }

    protected function normalizedMultilineSetting(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", trim($value));

        return $normalized !== '' ? $normalized : null;
    }
}
