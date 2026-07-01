<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => fake()->sentence(),
                'id' => fake()->sentence(),
            ],
            'is_published' => true,
            'category_id' => Category::factory()->forPosts(),
            'author_id' => User::factory(),
            'published_at' => now(),
        ];
    }
}
