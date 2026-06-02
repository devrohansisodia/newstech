<?php

namespace NewsTech\Menu\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Menu\Models\MenuGroup;

/**
 * @extends Factory<MenuGroup>
 */
class MenuGroupFactory extends Factory
{
    protected $model = MenuGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'location' => fake()->randomElement(array_keys(MenuGroup::locationOptions())),
            'status' => true,
        ];
    }
}
