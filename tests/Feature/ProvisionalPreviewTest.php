<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Currency;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Services\PreviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function grantEditPreviewPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo($permissions);
}

function provisionalUrlFromJs($component): string
{
    $js = $component->effects['xjs'] ?? [];

    foreach ($js as $entry) {
        if (($entry['expression'] ?? null) === '($url) => window.open($url, "_blank", "noopener")') {
            return $entry['params'][0] ?? '';
        }
    }

    return '';
}

it('previews unsaved page form data without persisting it', function () {
    $page = Page::factory()->create([
        'title' => ['en' => 'Saved Title'],
        'content' => ['en' => '<p>Saved content</p>'],
        'status' => ContentStatus::Draft,
    ]);

    $user = User::factory()->admin()->create();
    grantEditPreviewPermissions($user, ['ViewAny:Page', 'View:Page', 'Update:Page']);

    $component = Livewire::actingAs($user)
        ->test(EditPage::class, ['record' => $page->id])
        ->fillForm([
            'title' => 'Unsaved Preview Title',
            'content' => '<p>Unsaved preview content</p>',
            'status' => ContentStatus::Draft,
        ]);

    $component->callAction('preview')->assertHasNoErrors();

    $url = provisionalUrlFromJs($component);

    expect($url)->not->toBeEmpty();

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('Unsaved Preview Title')
        ->assertSee('Unsaved preview content');

    $dbTitle = $page->fresh()->getTranslations('title');
    expect($dbTitle['en'])->toBe('Saved Title');
});

it('previews unsaved post form data without persisting it', function () {
    $post = Post::factory()->create([
        'title' => ['en' => 'Saved Post Title'],
        'content' => ['en' => '<p>Saved post content</p>'],
        'status' => ContentStatus::Publish,
    ]);

    $user = User::factory()->admin()->create();
    grantEditPreviewPermissions($user, ['ViewAny:Post', 'View:Post', 'Update:Post']);

    $component = Livewire::actingAs($user)
        ->test(EditPost::class, ['record' => $post->id])
        ->fillForm([
            'title' => 'Unsaved Post Preview',
            'content' => '<p>Unsaved post preview body</p>',
            'status' => ContentStatus::Publish,
        ]);

    $component->callAction('preview')->assertHasNoErrors();

    $url = provisionalUrlFromJs($component);

    expect($url)->not->toBeEmpty();

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('Unsaved Post Preview')
        ->assertSee('Unsaved post preview body');

    $dbTitle = $post->fresh()->getTranslations('title');
    expect($dbTitle['en'])->toBe('Saved Post Title');
});

it('previews unsaved product form data without persisting it', function () {
    $currency = Currency::factory()->create();

    $product = Product::factory()->create([
        'title' => ['en' => 'Saved Product'],
        'description' => ['en' => '<p>Saved description</p>'],
        'status' => ContentStatus::Publish,
        'currency_id' => $currency->id,
    ]);

    $user = User::factory()->admin()->create();
    grantEditPreviewPermissions($user, ['ViewAny:Product', 'View:Product', 'Update:Product']);

    $component = Livewire::actingAs($user)
        ->test(EditProduct::class, ['record' => $product->id])
        ->fillForm([
            'title' => 'Unsaved Product Preview',
            'description' => '<p>Unsaved product description</p>',
            'status' => ContentStatus::Publish,
        ]);

    $component->callAction('preview')->assertHasNoErrors();

    $url = provisionalUrlFromJs($component);

    expect($url)->not->toBeEmpty();

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('Unsaved Product Preview');

    $dbTitle = $product->fresh()->getTranslations('title');
    expect($dbTitle['en'])->toBe('Saved Product');
});

it('rejects a provisional preview with an invalid signature', function () {
    $page = Page::factory()->create();

    $url = route('preview.provisional', ['key' => 'some-key']);

    $this->get($url)->assertStatus(401);
});

it('rejects a provisional preview when the cached data expired', function () {
    $page = Page::factory()->create(['title' => ['en' => 'Original']]);

    $url = app(PreviewService::class)->provisionalUrl(
        $page,
        ['title' => 'Changed'],
        'en',
    );

    Cache::flush();

    $this->get($url)->assertStatus(404);
});
