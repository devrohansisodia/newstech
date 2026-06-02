<?php

namespace NewsTech\Menu\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_group_id' => MenuGroup::factory(),
            'parent_id' => null,
            'type' => 'custom_url',
            'label' => fake()->words(2, true),
            'url' => '/'.fake()->slug(),
            'page_id' => null,
            'category_id' => null,
            'sort_order' => 0,
            'status' => true,
            'opens_in_new_tab' => false,
        ];
    }
}
