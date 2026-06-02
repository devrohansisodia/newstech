<?php

namespace NewsTech\Advertisement\Support;

class AdvertisementSlotManager
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        /** @var array<string, array<string, mixed>> $slots */
        $slots = config('newstech-advertisement.slots', []);

        return $slots;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function has(string $slotKey): bool
    {
        return array_key_exists($slotKey, $this->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slotKey): ?array
    {
        return $this->all()[$slotKey] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return collect($this->all())
            ->mapWithKeys(fn (array $slot, string $key): array => [$key => $slot['label'] ?? $key])
            ->all();
    }
}
