<?php

namespace NewsTech\Core\Models\Concerns;

trait HasStatusLabels
{
    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return defined('static::STATUS_LABELS')
            ? static::STATUS_LABELS
            : [];
    }

    public function getStatusLabel(string $attribute = 'status'): string
    {
        $status = (string) $this->getAttribute($attribute);

        if ($status === '') {
            return 'Unknown';
        }

        return static::statusLabels()[$status] ?? str($status)->replace(['-', '_'], ' ')->title()->toString();
    }
}
