<?php

namespace NewsTech\Advertisement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Advertisement\Models\Advertisement;

/**
 * @extends Factory<Advertisement>
 */
class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'type' => Advertisement::TYPE_IMAGE,
            'status' => Advertisement::STATUS_ACTIVE,
            'slot_key' => 'homepage_top',
            'title' => 'Sponsored placement',
            'image_path' => 'newstech/media/'.$this->faker->slug().'.jpg',
            'target_url' => 'https://example.com/campaign',
            'html_content' => null,
            'open_in_new_tab' => true,
            'nofollow' => false,
            'sponsored' => true,
            'starts_at' => null,
            'ends_at' => null,
            'priority' => 0,
            'impressions_count' => 0,
            'clicks_count' => 0,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function html(): self
    {
        return $this->state(fn (): array => [
            'type' => Advertisement::TYPE_HTML,
            'image_path' => null,
            'target_url' => null,
            'html_content' => '<div class="rounded-2xl border border-stone-200 bg-white p-6 text-center font-semibold text-stone-900">HTML Campaign</div>',
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => [
            'status' => Advertisement::STATUS_INACTIVE,
        ]);
    }

    public function scheduledFuture(): self
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->addDay(),
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (): array => [
            'ends_at' => now()->subDay(),
        ]);
    }
}
