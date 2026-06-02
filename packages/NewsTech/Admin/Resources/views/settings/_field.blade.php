@php
    $fieldNameMap = [
        'website.identity.site_name' => 'site_name',
        'website.identity.logo_path' => 'logo',
        'website.identity.footer_logo_path' => 'footer_logo',
        'website.homepage.layout' => 'homepage_layout',
        'website.homepage.sidebar_title' => 'homepage_sidebar_title',
        'website.homepage.sidebar_content' => 'homepage_sidebar_content',
        'website.homepage.sidebar_link_label' => 'homepage_sidebar_link_label',
        'website.homepage.sidebar_link_url' => 'homepage_sidebar_link_url',
        'comments.enabled' => 'comments_enabled',
        'comments.require_moderation' => 'require_moderation',
        'comments.guest_comments_enabled' => 'guest_comments_enabled',
        'comments.website_field_enabled' => 'website_field_enabled',
        'comments.min_comment_length' => 'min_comment_length',
        'comments.max_comment_length' => 'max_comment_length',
        'comments.max_links_per_comment' => 'max_links_per_comment',
        'comments.blocked_words' => 'blocked_words',
        'comments.blocked_emails' => 'blocked_emails',
        'comments.blocked_ips' => 'blocked_ips',
        'comments.auto_reject_spam' => 'auto_reject_spam',
        'comments.throttle_seconds' => 'throttle_seconds',
    ];

    $fieldKey = $field['key'];
    $fieldName = $fieldNameMap[$fieldKey] ?? str($fieldKey)->afterLast('.')->toString();
    $fieldType = $field['type'] ?? 'text';
    $fieldValue = $settingsValues[$fieldKey] ?? null;
    $previewUrl = null;

    if ($fieldType === 'image' && filled($fieldValue)) {
        $previewUrl = app(\NewsTech\Core\Support\MediaManager::class)->url($fieldValue);
    }
@endphp

@switch($fieldType)
    @case('textarea')
        <x-newstech-admin::form.textarea
            :name="$fieldName"
            :label="$field['label']"
            :value="$fieldValue"
            :placeholder="$field['placeholder'] ?? null"
            :hint="$field['hint'] ?? null"
            :rows="$field['rows'] ?? 5"
            :required="$field['required'] ?? false"
        />
        @break

    @case('select')
        <x-newstech-admin::form.select
            :name="$fieldName"
            :label="$field['label']"
            :options="$field['options'] ?? []"
            :value="$fieldValue"
            :hint="$field['hint'] ?? null"
            :required="$field['required'] ?? false"
        />
        @break

    @case('toggle')
        <x-newstech-admin::form.toggle
            :name="$fieldName"
            :label="$field['label']"
            :checked="(bool) $fieldValue"
            :hint="$field['hint'] ?? null"
        />
        @break

    @case('image')
        <x-newstech-admin::form.media-picker
            :name="$fieldName"
            :label="$field['label']"
            :hint="$field['hint'] ?? null"
            :value="$fieldValue"
            preview-label="Selected asset"
            empty-state="No file selected yet. The frontend will continue using its fallback brand mark until an image is saved."
        />
        @break

    @default
        <x-newstech-admin::form.input
            :name="$fieldName"
            :type="in_array($fieldType, ['url', 'number'], true) ? $fieldType : 'text'"
            :label="$field['label']"
            :value="$fieldValue"
            :placeholder="$field['placeholder'] ?? null"
            :hint="$field['hint'] ?? null"
            :required="$field['required'] ?? false"
        />
@endswitch
