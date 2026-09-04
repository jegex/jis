<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Currency;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Services\PreviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function grantPreviewPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo($permissions);
}

it('creates a page draft and previews it from the create page', function () {
    $user = User::factory()->admin()->create();
    grantPreviewPermissions($user, ['ViewAny:Page', 'Create:Page']);

    $component = Livewire::actingAs($user)
        ->test(CreatePage::class)
        ->fillForm([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'status' => ContentStatus::Publish->value,
        ]);

    $component->callAction('preview')->assertHasNoErrors();

    $page = Page::withoutGlobalScope('published')->first();

    expect($page)->not->toBeNull()
        ->and($page->status)->toBe(ContentStatus::Draft)
        ->and($page->getTranslation('title', 'en'))->toBe('Draft Page')
        ->and($page->slug)->toBe('draft-page');

    $previewUrl = app(PreviewService::class)->url($page, session()->get('spatie_translatable_active_locale'));

    $component
        ->assertRedirect(PageResource::getUrl('edit', ['record' => $page]))
        ->assertJs('($url) => window.open($url, "_blank", "noopener")', $previewUrl);
});

it('creates a post draft and previews it from the create page', function () {
    $user = User::factory()->admin()->create();
    grantPreviewPermissions($user, ['ViewAny:Post', 'Create:Post']);

    $component = Livewire::actingAs($user)
        ->test(CreatePost::class)
        ->fillForm([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'status' => ContentStatus::Publish->value,
        ]);

    $component->callAction('preview')->assertHasNoErrors();

    $post = Post::withoutGlobalScope('published')->first();

    expect($post)->not->toBeNull()
        ->and($post->status)->toBe(ContentStatus::Draft)
        ->and($post->getTranslation('title', 'en'))->toBe('Draft Post')
        ->and($post->slug)->toBe('draft-post')
        ->and($post->author_id)->toBe($user->id);

    $previewUrl = app(PreviewService::class)->url($post, session()->get('spatie_translatable_active_locale'));

    $component
        ->assertRedirect(PostResource::getUrl('edit', ['record' => $post]))
        ->assertJs('($url) => window.open($url, "_blank", "noopener")', $previewUrl);
});

it('creates a product draft and previews it from the create page', function () {
    Currency::factory()->create();

    $user = User::factory()->admin()->create();
    grantPreviewPermissions($user, ['ViewAny:Product', 'Create:Product']);

    $component = Livewire::actingAs($user)
        ->test(CreateProduct::class)
        ->fillForm([
            'title' => 'Draft Product',
            'slug' => 'draft-product',
            'price' => 100000,
            'status' => ContentStatus::Publish->value,
        ]);

    $component->callAction('preview')->assertHasNoErrors();

    $product = Product::withoutGlobalScope('published')->first();

    expect($product)->not->toBeNull()
        ->and($product->status)->toBe(ContentStatus::Draft)
        ->and($product->getTranslation('title', 'en'))->toBe('Draft Product')
        ->and($product->slug)->toBe('draft-product')
        ->and($product->price)->toEqual(100000);

    $previewUrl = app(PreviewService::class)->url($product, session()->get('spatie_translatable_active_locale'));

    $component
        ->assertRedirect(ProductResource::getUrl('edit', ['record' => $product]))
        ->assertJs('($url) => window.open($url, "_blank", "noopener")', $previewUrl);
});

it('does not save a draft when the form is invalid', function () {
    $user = User::factory()->admin()->create();
    grantPreviewPermissions($user, ['ViewAny:Page', 'Create:Page']);

    Livewire::actingAs($user)
        ->test(CreatePage::class)
        ->callAction('preview')
        ->assertHasErrors(['data.title', 'data.slug']);

    expect(Page::withoutGlobalScope('published')->count())->toBe(0);
});
