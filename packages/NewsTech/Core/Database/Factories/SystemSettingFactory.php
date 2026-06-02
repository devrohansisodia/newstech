<?php

namespace NewsTech\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Core\Models\SystemSetting;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition(): array
    {
        return [
            'key' => 'settings.'.$this->faker->unique()->slug(3),
            'value' => $this->faker->sentence(),
        ];
    }
}
