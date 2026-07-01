<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => [app()->getLocale() => fake()->word()],
            'slug' => fake()->slug(),
            'type' => CategoryType::Product->value,
            'is_published' => true,
        ];
    }

    public function forPosts(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoryType::Post->value,
        ]);
    }

    public function forProducts(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoryType::Product->value,
        ]);
    }
}
