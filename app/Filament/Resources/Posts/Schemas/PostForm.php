<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\SeoSchema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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
                            TextInput::make('title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($livewire, $state, $get, $set) {
                                    if (! empty($get('slug'))) {
                                        return;
                                    }
                                    $set('slug', str($state)->slug());
                                })
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->required()
                                ->unique()
                                ->maxLength(255),
                            Textarea::make('excerpt'),
                            MyRichEditor::make('content'),
                        ]),
                ])->columnSpan(2),

                Group::make([
                    Section::make('Publishing')
                        ->collapsible()
                        ->schema([
                            Toggle::make('is_published')
                                ->label('Published')
                                ->default(false)
                                ->visible(fn (): bool => auth()->user()?->can('Publish:Post') ?? false),

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
