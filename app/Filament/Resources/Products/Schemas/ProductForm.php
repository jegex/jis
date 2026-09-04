<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Actions\GenerateSlug;
use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Filament\Schemas\Components\MoneyInput;
use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\Components\TitleWithSlug;
use App\Filament\Schemas\SeoSchema;
use App\Models\Category;
use App\Models\Currency;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                            TitleWithSlug::make(),
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
                            Select::make('status')
                                ->options(ContentStatus::class)
                                ->default(ContentStatus::Draft->value)
                                ->visible(fn (): bool => auth()->user()?->can('Publish:Product') ?? false)
                                ->live(),

                            DateTimePicker::make('scheduled_at')
                                ->label('Schedule Publish')
                                ->native(false)
                                ->timezone(config('app.timezone'))
                                ->visible(fn (Get $get): bool => $get('status') === ContentStatus::Future->value),

                            DateTimePicker::make('release_date')
                                ->label('Release Date')
                                ->helperText('Leave empty for regular products. Set a future date for preorders.')
                                ->native(false)
                                ->timezone(config('app.timezone')),

                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            if (blank($get('slug'))) {
                                                $set('slug', GenerateSlug::run($state, Category::class, app()->getLocale()));
                                            }
                                        }),
                                    TextInput::make('slug')
                                        ->required()
                                        ->unique(
                                            Category::class,
                                            modifyRuleUsing: fn ($rule) => $rule->where('type', CategoryType::Product->value)
                                        )
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
