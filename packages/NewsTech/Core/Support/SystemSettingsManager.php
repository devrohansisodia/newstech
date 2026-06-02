<?php

namespace NewsTech\Core\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use NewsTech\Core\Repositories\SystemSettingRepository;
use Throwable;

class SystemSettingsManager
{
    /**
     * @var array<string, string>
     */
    protected array $configMap = [
        'website.identity.site_name' => 'newstech.brand.name',
        'website.identity.logo_path' => 'newstech.brand.logo_path',
        'website.identity.footer_logo_path' => 'newstech.brand.footer_logo_path',
        'website.homepage.layout' => 'newstech.homepage.layout',
        'website.homepage.sidebar_title' => 'newstech.homepage.sidebar_title',
        'website.homepage.sidebar_content' => 'newstech.homepage.sidebar_content',
        'website.homepage.sidebar_link_label' => 'newstech.homepage.sidebar_link_label',
        'website.homepage.sidebar_link_url' => 'newstech.homepage.sidebar_link_url',
        'comments.enabled' => 'newstech-comment.enabled',
        'comments.require_moderation' => 'newstech-comment.require_moderation',
        'comments.guest_comments_enabled' => 'newstech-comment.guest_comments_enabled',
        'comments.website_field_enabled' => 'newstech-comment.website_field_enabled',
        'comments.min_comment_length' => 'newstech-comment.min_comment_length',
        'comments.max_comment_length' => 'newstech-comment.max_comment_length',
        'comments.max_links_per_comment' => 'newstech-comment.max_links_per_comment',
        'comments.blocked_words' => 'newstech-comment.blocked_words',
        'comments.blocked_emails' => 'newstech-comment.blocked_emails',
        'comments.blocked_ips' => 'newstech-comment.blocked_ips',
        'comments.auto_reject_spam' => 'newstech-comment.auto_reject_spam',
        'comments.throttle_seconds' => 'newstech-comment.throttle_seconds',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $defaultConfigValues = [
        'newstech.brand.name' => 'NewsTech',
        'newstech.brand.logo_path' => null,
        'newstech.brand.footer_logo_path' => null,
        'newstech.homepage.layout' => 'full_width',
        'newstech.homepage.sidebar_title' => null,
        'newstech.homepage.sidebar_content' => null,
        'newstech.homepage.sidebar_link_label' => null,
        'newstech.homepage.sidebar_link_url' => null,
        'newstech-comment.enabled' => true,
        'newstech-comment.require_moderation' => true,
        'newstech-comment.guest_comments_enabled' => true,
        'newstech-comment.website_field_enabled' => true,
        'newstech-comment.min_comment_length' => 5,
        'newstech-comment.max_comment_length' => 2000,
        'newstech-comment.max_links_per_comment' => 2,
        'newstech-comment.blocked_words' => '',
        'newstech-comment.blocked_emails' => '',
        'newstech-comment.blocked_ips' => '',
        'newstech-comment.auto_reject_spam' => false,
        'newstech-comment.throttle_seconds' => 60,
    ];

    public function __construct(protected SystemSettingRepository $settings) {}

    /**
     * @param  array<string, string>  $configMap
     * @param  array<string, mixed>  $defaultConfigValues
     */
    public function registerConfigMap(array $configMap, array $defaultConfigValues = []): void
    {
        $this->configMap = [
            ...$this->configMap,
            ...$configMap,
        ];

        $this->defaultConfigValues = [
            ...$this->defaultConfigValues,
            ...$defaultConfigValues,
        ];
    }

    public function bootConfig(): void
    {
        foreach ($this->configMap as $settingKey => $configKey) {
            $defaultValue = $this->defaultValue($configKey);
            $value = $this->get($settingKey, $defaultValue);

            Config::set($configKey, $this->resolvedConfigValue($value, $defaultValue));
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values()->get($key, $default);
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $configKey = $this->configMap[$key] ?? null;
            $values[$key] = $this->get($key, $configKey ? Config::get($configKey) : null);
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        $normalizedValues = [];

        foreach ($values as $key => $value) {
            $normalizedValues[$key] = $this->normalizeValueForStorage($value);
        }

        $this->settings->setMany($normalizedValues);
        $this->bootConfig();
    }

    /**
     * @return Collection<string, string|null>
     */
    protected function values(): Collection
    {
        if (! $this->settingsTableExists()) {
            return collect();
        }

        return $this->settings->keyedValues();
    }

    protected function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('system_settings');
        } catch (Throwable) {
            return false;
        }
    }

    protected function defaultValue(string $configKey): mixed
    {
        return Config::get($configKey, $this->defaultConfigValues[$configKey] ?? null);
    }

    protected function resolvedConfigValue(mixed $value, mixed $defaultValue): mixed
    {
        if ($value === '' || $value === null) {
            return $defaultValue;
        }

        return match (gettype($defaultValue)) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $defaultValue,
            'integer' => is_numeric($value) ? (int) $value : $defaultValue,
            'double' => is_numeric($value) ? (float) $value : $defaultValue,
            default => $value,
        };
    }

    protected function normalizeValueForStorage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return $value;
        }

        return (string) $value;
    }
}
