<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\URL;

it('renders an unpublished page preview with signed url', function () {
    Page::factory()->create(['status' => ContentStatus::Draft, 'title' => 'Secret Draft Page']);

    $page = Page::withoutGlobalScope('published')->first();
    $url = URL::temporarySignedRoute('preview.show', now()->addMinutes(30), [
        'type' => 'page',
        'record' => $page->id,
        'preview' => 1,
    ]);

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('Preview mode')
        ->assertSee('Secret Draft Page');
});

it('renders an unpublished post preview with signed url', function () {
    Post::factory()->create(['status' => ContentStatus::Draft, 'title' => 'Secret Draft Post']);

    $post = Post::withoutGlobalScope('published')->first();
    $url = URL::temporarySignedRoute('preview.show', now()->addMinutes(30), [
        'type' => 'post',
        'record' => $post->id,
        'preview' => 1,
    ]);

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('Preview mode')
        ->assertSee('Secret Draft Post');
});

it('renders an unpublished product preview with signed url', function () {
    Product::factory()->create(['status' => ContentStatus::Draft, 'title' => 'Secret Draft Product']);

    $product = Product::withoutGlobalScope('published')->first();
    $url = URL::temporarySignedRoute('preview.show', now()->addMinutes(30), [
        'type' => 'product',
        'record' => $product->id,
        'preview' => 1,
    ]);

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('Preview mode')
        ->assertSee('Secret Draft Product');
});

it('rejects preview requests without a valid signature', function () {
    Page::factory()->create(['status' => ContentStatus::Draft]);

    $page = Page::withoutGlobalScope('published')->first();
    $url = route('preview.show', ['type' => 'page', 'record' => $page->id]);

    $this->get($url)->assertStatus(401);
});

it('rejects preview requests with a tampered signature', function () {
    Page::factory()->create(['status' => ContentStatus::Draft]);

    $page = Page::withoutGlobalScope('published')->first();
    $url = URL::temporarySignedRoute('preview.show', now()->addMinutes(30), [
        'type' => 'page',
        'record' => $page->id,
        'preview' => 1,
    ]);

    $this->get($url.'&tampered=true')
        ->assertStatus(401);
});

it('does not expose an unpublished page directly on the public route', function () {
    Page::factory()->create(['status' => ContentStatus::Draft]);

    $page = Page::withoutGlobalScope('published')->first();

    $this->get('/pages/'.$page->slug)->assertStatus(404);
});

it('shows preview banner only in preview mode, not on public pages', function () {
    Page::factory()->create(['title' => 'Published Page']);

    $this->get('/pages/'.Page::first()->slug)
        ->assertSuccessful()
        ->assertDontSee('Preview mode');
});
