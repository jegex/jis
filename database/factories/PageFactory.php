<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => fake()->sentence(),
                'id' => fake()->sentence(),
            ],
            'is_published' => true,
        ];
    }
}
