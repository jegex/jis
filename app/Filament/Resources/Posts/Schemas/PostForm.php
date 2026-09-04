<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\ContentStatus;
use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\Components\TitleWithSlug;
use App\Filament\Schemas\SeoSchema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('General')
                        ->columns(1)
                        ->schema([
                            TitleWithSlug::make(),
                            Textarea::make('excerpt'),
                            MyRichEditor::make('content'),
                        ]),
                ])->columnSpan(2),

                Group::make([
                    Section::make('Publishing')
                        ->collapsible()
                        ->schema([
                            Select::make('status')
                                ->options(ContentStatus::class)
                                ->default(ContentStatus::Draft->value)
                                ->visible(fn (): bool => auth()->user()?->can('Publish:Post') ?? false)
                                ->live(),

                            DateTimePicker::make('scheduled_at')
                                ->label('Schedule Publish')
                                ->native(false)
                                ->timezone(config('app.timezone'))
                                ->visible(fn (Get $get): bool => $get('status') === ContentStatus::Future->value),

                            DateTimePicker::make('published_at')
                                ->default(now()),

                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('author_id')
                                ->default(auth()->user()->id)
                                ->relationship('author', 'name')
                                ->searchable()
                                ->preload(),
                        ]),

                    Section::make('Featured Image')
                        ->collapsible()
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('featured_image')
                                ->collection('featured_image')
                                ->image()
                                ->maxSize(2048),
                        ]),

                    Section::make('SEO')
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
                ])->columnSpan(1),
            ]);
    }
}
