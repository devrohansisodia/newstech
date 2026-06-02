<?php

namespace NewsTech\Admin\Support;

use Illuminate\Http\Request;
use NewsTech\Core\Support\SystemSettingsManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SettingsGroupManager
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $groups = [];

    /**
     * @param  array<string, mixed>  $definition
     */
    public function register(array $definition): void
    {
        $key = (string) ($definition['key'] ?? '');

        if ($key === '') {
            throw new \InvalidArgumentException('Settings groups must define a key.');
        }

        $sections = array_values($definition['sections'] ?? []);

        $this->groups[$key] = [
            'key' => $key,
            'title' => (string) ($definition['title'] ?? $key),
            'description' => (string) ($definition['description'] ?? ''),
            'icon' => (string) ($definition['icon'] ?? ''),
            'sort' => (int) ($definition['sort'] ?? 0),
            'sections' => $sections,
            'setting_keys' => array_values($definition['setting_keys'] ?? $this->extractSettingKeys($sections)),
            'rules' => $definition['rules'] ?? [],
            'messages' => $definition['messages'] ?? [],
            'attributes' => $definition['attributes'] ?? [],
            'save' => $definition['save'] ?? null,
            'summary' => $definition['summary'] ?? null,
            'empty_state_title' => (string) ($definition['empty_state_title'] ?? 'No settings available yet'),
            'empty_state_description' => (string) ($definition['empty_state_description'] ?? 'This settings group is registered and ready for future package-owned controls.'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $groups = array_values($this->groups);

        usort($groups, fn (array $left, array $right): int => [$left['sort'], $left['title']] <=> [$right['sort'], $right['title']]);

        return $groups;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        return $this->groups[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function findOrFail(string $key): array
    {
        $group = $this->find($key);

        if ($group === null) {
            throw new NotFoundHttpException;
        }

        return $group;
    }

    /**
     * @return array<string, mixed>
     */
    public function values(array $group, SystemSettingsManager $settingsManager): array
    {
        return $settingsManager->only($group['setting_keys']);
    }

    /**
     * @return array<string, mixed>
     */
    public function validate(array $group, array $input): array
    {
        return validator(
            $input,
            $group['rules'],
            $group['messages'],
            $group['attributes'],
        )->validate();
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<string, mixed>  $validated
     */
    public function save(array $group, Request $request, array $validated): void
    {
        if (! is_callable($group['save']) && ! is_string($group['save']) && ! is_array($group['save'])) {
            return;
        }

        app()->call($group['save'], [
            'request' => $request,
            'validated' => $validated,
            'group' => $group,
        ]);
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<string, mixed>  $settingsValues
     */
    public function summary(array $group, array $settingsValues): ?string
    {
        if (! is_callable($group['summary']) && ! is_string($group['summary']) && ! is_array($group['summary'])) {
            return null;
        }

        $summary = app()->call($group['summary'], [
            'settingsValues' => $settingsValues,
            'group' => $group,
        ]);

        return is_string($summary) && $summary !== '' ? $summary : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, string>
     */
    protected function extractSettingKeys(array $sections): array
    {
        $keys = [];

        foreach ($sections as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (is_string($field['key'] ?? null) && $field['key'] !== '') {
                    $keys[] = $field['key'];
                }
            }
        }

        return $keys;
    }
}
