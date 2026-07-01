<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => fake()->sentence(3),
                'id' => fake()->sentence(3),
            ],
            'price' => fake()->numberBetween(50000, 500000),
            'is_published' => true,
            'category_id' => Category::factory()->forProducts(),
            'currency_id' => Currency::factory(),
        ];
    }
}
