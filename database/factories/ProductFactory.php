<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\FileLocation;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'status' => ContentStatus::Publish,
            'file_location' => FileLocation::Local,
            'category_id' => Category::factory()->forProducts(),
            'currency_id' => Currency::factory(),
        ];
    }

    public function external(string $url = 'https://example.com/download'): static
    {
        return $this->state([
            'file_location' => FileLocation::External,
            'url_external' => $url,
        ]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ContentStatus::Draft]);
    }

    public function pending(): static
    {
        return $this->state(['status' => ContentStatus::Pending]);
    }

    public function scheduled(?Carbon $at = null): static
    {
        return $this->state([
            'status' => ContentStatus::Future,
            'scheduled_at' => $at ?? now()->addDay(),
        ]);
    }
}
