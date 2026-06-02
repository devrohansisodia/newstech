<?php

namespace NewsTech\Core\Repositories;

use Illuminate\Support\Collection;
use NewsTech\Core\Models\SystemSetting;

/**
 * @extends BaseRepository<SystemSetting>
 */
class SystemSettingRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return SystemSetting::class;
    }

    /**
     * @return Collection<string, string|null>
     */
    public function keyedValues(): Collection
    {
        return $this->query()
            ->orderBy('key')
            ->get(['key', 'value'])
            ->pluck('value', 'key');
    }

    public function set(string $key, ?string $value): SystemSetting
    {
        /** @var SystemSetting $setting */
        $setting = $this->query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        return $setting;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }
}
