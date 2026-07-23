<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\CategoryType;
use App\Filament\Schemas\Components\MoneyInput;
use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\SeoSchema;
use App\Models\Category;
use App\Models\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class ProductForm
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
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('short_description'),
                            MyRichEditor::make('description'),
                        ]),

                    Section::make('Media')
                        ->collapsible()
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('cover')
                                ->downloadable()
                                ->disk('public')
                                ->collection('cover')
                                ->image()
                                ->maxSize(2048)
                                ->label('Cover Image'),

                            SpatieMediaLibraryFileUpload::make('gallery')
                                ->downloadable()
                                ->disk('public')
                                ->collection('gallery')
                                ->multiple()
                                ->image()
                                ->panelLayout('grid')
                                ->maxSize(2048),
                        ]),
                ])->columnSpan(2),

                Group::make([
                    Section::make('Availability')
                        ->collapsible()
                        ->schema([
                            Toggle::make('is_published')
                                ->label('Published')
                                ->visible(fn (): bool => auth()->user()?->can('Publish:Product') ?? false)
                                ->default(false),

                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                    TextInput::make('slug')
                                        ->required()
                                        ->unique(Category::class)
                                        ->maxLength(255),
                                    Toggle::make('is_published')
                                        ->default(true),
                                    Textarea::make('description')
                                        ->maxLength(65535),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $data['type'] = CategoryType::Product->value;

                                    return Category::create($data)->getKey();
                                }),
                        ]),

                    Section::make('Price & File')
                        ->collapsible()
                        ->schema([
                            Select::make('currency_id')
                                ->live()
                                ->default(Currency::getDefault()->id)
                                ->relationship('currency', 'code'),

                            MoneyInput::make('price')
                                ->prefix(fn ($get) => Currency::find($get('currency_id'))->code),

                            SpatieMediaLibraryFileUpload::make('file')
                                ->disk('local')
                                ->preserveFilenames()
                                ->collection('file')
                                ->previewable(false)
                                ->downloadable()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'application/epub+zip',
                                    'application/zip',
                                    'application/x-zip-compressed',
                                    'application/zip-compressed',
                                    'multipart/x-zip',
                                    'application/x-zip',
                                    'application/x-rar-compressed',
                                    'application/x-7z-compressed',
                                    'application/x-7z',
                                ])
                                ->label('Product File'),
                        ]),

                    Section::make('SEO')
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
                ])->columnSpan(1),
            ]);
    }
}
