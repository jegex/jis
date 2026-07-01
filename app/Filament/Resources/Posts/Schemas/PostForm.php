<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
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
                    TranslatableTabs::make('General')
                        ->columns(1)
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('excerpt'),
                            MyRichEditor::make('content'),
                        ]),

                    Section::make('Media')
                        ->collapsible()
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('featured_image')
                                ->collection('featured_image')
                                ->image()
                                ->maxSize(2048),
                        ]),
                ])->columnSpan(2),

                Group::make([
                    Section::make('Publishing')
                        ->collapsible()
                        ->schema([
                            Toggle::make('is_published')
                                ->label('Published')
                                ->default(false),

                            DateTimePicker::make('published_at')
                                ->default(now()),

                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('author_id')
                                ->relationship('author', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                ])->columnSpan(1),

                Section::make('SEO')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
            ]);
    }
}
