<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Pages\PostRevisions;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

final class PostResource extends Resource
{
    use Translatable;

    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = -1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user?->can('Publish:Post') === false) {
            $data['status'] = ContentStatus::Draft->value;
        }

        $data['author_id'] = $user?->id;

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScope('published');
        $user = auth()->user();

        if ($user && ! $user->can('ViewAny:Post') && $user->can('ViewOwn:Post')) {
            $query->where('author_id', $user->id);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
            'revisions' => PostRevisions::route('/{record}/revisions'),
        ];
    }
}
