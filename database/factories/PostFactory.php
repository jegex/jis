<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'status' => ContentStatus::Publish,
            'category_id' => Category::factory()->forPosts(),
            'author_id' => User::factory(),
            'published_at' => now(),
        ];
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
