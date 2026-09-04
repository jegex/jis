<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\PageRevisions;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function grantPagePermissions(User $user): void
{
    foreach (['ViewAny:Page', 'View:Page', 'Update:Page', 'Create:Page'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo(['ViewAny:Page', 'View:Page', 'Update:Page', 'Create:Page']);
}

it('records a created revision when a page is created', function () {
    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    Livewire::actingAs($user)
        ->test(CreatePage::class)
        ->fillForm([
            'title' => 'Brand New',
            'slug' => 'brand-new',
            'status' => ContentStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $page = Page::withoutGlobalScope('published')->latest('id')->first();

    $activity = $page->activitiesAsSubject()->forEvent('created')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id);
});

it('records an updated revision when a page is edited', function () {
    $page = Page::factory()->create(['title' => ['en' => 'Original Title']]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    Livewire::actingAs($user)
        ->test(EditPage::class, ['record' => $page->id])
        ->fillForm([
            'title' => 'Updated Title',
            'status' => ContentStatus::Draft,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $activity = $page->fresh()->activitiesAsSubject()->forEvent('updated')->latest()->first();

    $newTitle = json_decode(data_get($activity->attribute_changes, 'attributes.title'), true);
    $oldTitle = json_decode(data_get($activity->attribute_changes, 'old.title'), true);

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and(is_array($newTitle) ? ($newTitle['en'] ?? null) : $newTitle)
        ->toBe('Updated Title')
        ->and(is_array($oldTitle) ? ($oldTitle['en'] ?? null) : $oldTitle)
        ->toBe('Original Title');
});

it('records all locales for translatable fields', function () {
    $page = Page::factory()->create([
        'title' => ['en' => 'Hello', 'id' => 'Halo'],
    ]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    Livewire::actingAs($user)
        ->test(EditPage::class, ['record' => $page->id])
        ->fillForm([
            'title' => 'Hello',
            'status' => ContentStatus::Draft,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $activity = $page->fresh()->activitiesAsSubject()->forEvent('updated')->latest()->first();

    $newTitleRaw = data_get($activity->attribute_changes, 'attributes.title');
    $newTitle = is_string($newTitleRaw) ? json_decode($newTitleRaw, true) : $newTitleRaw;

    expect($newTitle)->not->toBeNull()
        ->and($newTitle)->toHaveKeys(['en', 'id'])
        ->and($newTitle['en'])->toBe('Hello')
        ->and($newTitle['id'])->toBe('Halo');
});

it('records a revision on every save', function () {
    $page = Page::factory()->create([
        'title' => ['en' => 'Same Title'],
        'content' => ['en' => '<p></p>'],
    ]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    Livewire::actingAs($user)
        ->test(EditPage::class, ['record' => $page->id])
        ->fillForm([
            'title' => 'Same Title',
            'content' => '<p></p>',
            'status' => ContentStatus::Publish,
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($page->fresh()->activitiesAsSubject()->forEvent('updated')->count())->toBe(1);
});

it('allows a user with view permission to access the revisions page', function () {
    $page = Page::factory()->create(['title' => ['en' => 'Hello']]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    $this->actingAs($user)
        ->get('/admin/pages/'.$page->id.'/revisions')
        ->assertSuccessful()
        ->assertSee('Revisions');
});

it('denies a user without view permission', function () {
    $page = Page::factory()->create();

    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get('/admin/pages/'.$page->id.'/revisions')
        ->assertForbidden();
});

it('lists the recorded revisions', function () {
    $page = Page::factory()->create(['title' => ['en' => 'Hello']]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    $this->actingAs($user)
        ->get('/admin/pages/'.$page->id.'/revisions')
        ->assertSuccessful()
        ->assertSee('Created');
});

it('compares two revisions and shows the differences', function () {
    $page = Page::factory()->create([
        'title' => ['en' => 'Hello'],
        'content' => ['en' => 'First version'],
    ]);

    $page->update(['content' => ['en' => 'Second version']]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    Livewire::actingAs($user)
        ->test(PageRevisions::class, ['record' => $page->id])
        ->assertSee('Content')
        ->assertSeeHtml('<del>First</del>')
        ->assertSeeHtml('<ins>Second</ins>');
});

it('restores the record to the selected revision', function () {
    $page = Page::factory()->create([
        'title' => ['en' => 'Original Title'],
        'content' => ['en' => 'Original content'],
    ]);

    $page->update([
        'title' => ['en' => 'Changed Title'],
        'content' => ['en' => 'Changed content'],
    ]);

    $user = User::factory()->admin()->create();
    grantPagePermissions($user);

    $created = $page->activitiesAsSubject()->forEvent('created')->first();

    Livewire::actingAs($user)
        ->test(PageRevisions::class, ['record' => $page->id])
        ->set('rightId', $created->id)
        ->call('restoreRevision')
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->getTranslations('title')['en'])->toBe('Original Title')
        ->and($page->getTranslations('content')['en'])->toBe('Original content')
        ->and($page->activitiesAsSubject()->forEvent('updated')->latest('id')->first())->not->toBeNull();
});

it('denies restoring without the update permission', function () {
    $page = Page::factory()->create([
        'title' => ['en' => 'Original Title'],
    ]);

    $page->update(['title' => ['en' => 'Changed Title']]);

    $user = User::factory()->admin()->create();

    foreach (['ViewAny:Page', 'View:Page'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo(['ViewAny:Page', 'View:Page']);

    $created = $page->activitiesAsSubject()->forEvent('created')->first();

    $component = Livewire::actingAs($user)
        ->test(PageRevisions::class, ['record' => $page->id])
        ->set('rightId', $created->id);

    expect($component->target->canRestore())->toBeFalse();

    $component->call('restoreRevision');

    expect($page->fresh()->getTranslations('title')['en'])->toBe('Changed Title');
});
