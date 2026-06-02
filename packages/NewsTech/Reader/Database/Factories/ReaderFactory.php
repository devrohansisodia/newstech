<?php

namespace NewsTech\Reader\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NewsTech\Reader\Models\Reader;

/**
 * @extends Factory<Reader>
 */
class ReaderFactory extends Factory
{
    protected $model = Reader::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => null,
            'avatar' => null,
            'bio' => null,
            'website' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
