<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('publishes scheduled content whose time has come', function () {
    $due = now()->subMinute();

    Page::factory()->scheduled($due)->create();
    Post::factory()->scheduled($due)->create(['published_at' => null]);
    Product::factory()->scheduled($due)->create();

    $this->artisan('content:publish-scheduled')->assertSuccessful();

    expect(Page::withoutGlobalScope('published')->first()->status)->toBe(ContentStatus::Publish)
        ->and(Post::withoutGlobalScope('published')->first()->status)->toBe(ContentStatus::Publish)
        ->and(Post::withoutGlobalScope('published')->first()->published_at)->not->toBeNull()
        ->and(Product::withoutGlobalScope('published')->first()->status)->toBe(ContentStatus::Publish);
});

it('keeps future content on hold until scheduled_at passes', function () {
    Post::factory()->scheduled(now()->addDay())->create();

    $this->artisan('content:publish-scheduled')->assertSuccessful();

    expect(Post::withoutGlobalScope('published')->first()->status)->toBe(ContentStatus::Future);
});

it('hides non-published content from default queries', function () {
    Page::factory()->draft()->create();
    Post::factory()->draft()->create();
    Product::factory()->draft()->create();

    expect(Page::query()->count())->toBe(0)
        ->and(Post::query()->count())->toBe(0)
        ->and(Product::query()->count())->toBe(0);
});
