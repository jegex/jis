<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Supports\SettingPage;
use App\Models\Category;
use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class HomepageBuilder extends SettingPage
{
    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    public static function getNavigationLabel(): string
    {
        return 'Homepage';
    }

    public function form(Schema $schema): Schema
    {
        $productCategories = Category::where('type', 'product')->pluck('name', 'id')->toArray();
        $postCategories = Category::where('type', 'post')->pluck('name', 'id')->toArray();

        return $schema
            ->components([
                Builder::make('homepage_blocks')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->collapsed()
                    ->blocks([
                        $this->heroBlock(),
                        $this->statsBlock(),
                        $this->featuredBlock(),
                        $this->servicesBlock(),
                        $this->projectsBlock(),
                        $this->featuredProductsBlock($productCategories),
                        $this->latestPostsBlock($postCategories),
                        $this->ctaBlock(),
                        $this->testimonialsBlock(),
                        $this->partnersBlock(),
                    ])
                    ->collapsible()
                    ->blockNumbers(false)
                    ->addActionLabel('Add Section'),
            ]);
    }

    public function getTitle(): string
    {
        return 'Homepage Builder';
    }

    private static function iconOptions(): array
    {
        return [
            Heroicon::PencilSquare->value => 'Pencil',
            Heroicon::Cube->value => 'Cube',
            Heroicon::ArrowsRightLeft->value => 'Arrows',
            Heroicon::Beaker->value => 'Beaker',
            Heroicon::Link->value => 'Link',
            Heroicon::AcademicCap->value => 'Academic Cap',
            Heroicon::Bolt->value => 'Bolt',
            Heroicon::ChartBar->value => 'Chart Bar',
            Heroicon::ClipboardDocument->value => 'Clipboard',
            Heroicon::Cog->value => 'Cog',
            Heroicon::ComputerDesktop->value => 'Desktop',
            Heroicon::CurrencyDollar->value => 'Dollar',
            Heroicon::Eye->value => 'Eye',
            Heroicon::GlobeAlt->value => 'Globe',
            Heroicon::LightBulb->value => 'Light Bulb',
            Heroicon::MagnifyingGlass->value => 'Magnifying Glass',
            Heroicon::Phone->value => 'Phone',
            Heroicon::RocketLaunch->value => 'Rocket',
            Heroicon::Scale->value => 'Scale',
            Heroicon::ShieldCheck->value => 'Shield',
            Heroicon::Square3Stack3d->value => 'Stack 3D',
            Heroicon::Star->value => 'Star',
            Heroicon::Truck->value => 'Truck',
            Heroicon::Users->value => 'Users',
            Heroicon::Wrench->value => 'Wrench',
        ];
    }

    private static function sectionHeader(): array
    {
        return [
            TranslatableTabs::make('Section Header')
                ->schema([
                    TextInput::make('label')
                        ->label('Section Label'),
                    TextInput::make('title')
                        ->label('Section Title'),
                    Textarea::make('description')
                        ->label('Section Description')
                        ->rows(2),
                ]),
        ];
    }

    private function heroBlock(): Block
    {
        return Block::make('hero')
            ->label('Hero')
            ->icon('heroicon-o-home')
            ->schema([
                Toggle::make('badge_enabled')
                    ->label('Show Badge')
                    ->live()
                    ->default(true),
                TranslatableTabs::make('Hero Content')
                    ->schema([
                        TextInput::make('badge')
                            ->label('Badge Text')
                            ->hidden(fn (Get $get) => ! $get('badge_enabled')),
                        TextInput::make('title')
                            ->label('Title')
                            ->required(),
                        Textarea::make('subtitle')
                            ->label('Subtitle')
                            ->rows(2),
                    ]),
                TranslatableTabs::make('Hero Buttons')
                    ->schema([
                        TextInput::make('primary_button_label')
                            ->label('Primary Button Label'),
                        TextInput::make('secondary_button_label')
                            ->label('Secondary Button Label'),
                    ]),
                TextInput::make('primary_button_url')
                    ->label('Primary Button URL')
                    ->default('#services'),
                TextInput::make('secondary_button_url')
                    ->label('Secondary Button URL')
                    ->default('#contact'),
                TextInput::make('image')
                    ->label('Hero Image Path')
                    ->helperText('Path relative to public/')
                    ->default('images/hero.png'),
            ]);
    }

    private function statsBlock(): Block
    {
        return Block::make('stats')
            ->label('Stats')
            ->icon('heroicon-o-chart-bar')
            ->schema([
                Repeater::make('items')
                    ->label('Statistics')
                    ->collapsible()
                    ->itemLabel(fn ($state) => $state['label'][app()->getLocale()] ?? '')
                    ->schema([
                        Group::make()
                            ->columns(3)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('icon')
                                    ->label('Icon')
                                    ->options(self::iconOptions())
                                    ->searchable()
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Value')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('suffix')
                                    ->label('Suffix (e.g. +, %)'),
                            ]),
                        TranslatableTabs::make()
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(2)
                    ->defaultItems(4)
                    ->addable(true)
                    ->reorderable(true),
            ]);
    }

    private function featuredBlock(): Block
    {
        return Block::make('featured')
            ->label('Featured')
            ->icon('heroicon-o-star')
            ->schema([
                ...self::sectionHeader(),
                Repeater::make('services')
                    ->label('Featured Services')
                    ->collapsed()
                    ->itemLabel(fn ($state) => $state['title'][app()->getLocale()] ?? '')
                    ->schema([
                        Select::make('icon')
                            ->label('Icon')
                            ->options(self::iconOptions())
                            ->searchable()
                            ->columnSpanFull()
                            ->required(),
                        TranslatableTabs::make()
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Title')
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2),
                            ]),
                    ])
                    ->columns(2)
                    ->defaultItems(4)
                    ->addable(true)
                    ->reorderable(true),
            ]);
    }

    private function servicesBlock(): Block
    {
        return Block::make('services')
            ->label('Services')
            ->icon('heroicon-o-cog')
            ->schema([
                ...self::sectionHeader(),
                Repeater::make('items')
                    ->label('Services')
                    ->collapsed()
                    ->itemLabel(fn ($state) => $state['title'][app()->getLocale()] ?? '')
                    ->schema([
                        Select::make('icon')
                            ->label('Icon')
                            ->options(self::iconOptions())
                            ->searchable()
                            ->columnSpanFull()
                            ->required(),
                        TranslatableTabs::make()
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('title')
                                    ->required(),
                                Textarea::make('description')
                                    ->rows(2),
                            ]),
                    ])
                    ->columns(2)
                    ->defaultItems(5)
                    ->addable(true)
                    ->reorderable(true),
            ]);
    }

    private function projectsBlock(): Block
    {
        return Block::make('projects')
            ->label('Projects')
            ->icon('heroicon-o-briefcase')
            ->schema([
                ...self::sectionHeader(),
                TextInput::make('count')
                    ->label('Number of Projects')
                    ->numeric()
                    ->helperText('Leave empty to show all'),
                Select::make('sort_by')
                    ->label('Sort By')
                    ->options([
                        'latest' => 'Latest',
                        'name_asc' => 'Name (A-Z)',
                        'name_desc' => 'Name (Z-A)',
                    ])
                    ->default('latest'),
            ]);
    }

    private function featuredProductsBlock(array $productCategories): Block
    {
        return Block::make('featured-products')
            ->label('Featured Products')
            ->icon('heroicon-o-shopping-bag')
            ->schema([
                ...self::sectionHeader(),
                TextInput::make('count')
                    ->label('Number of Products')
                    ->numeric()
                    ->default(6),
                Select::make('sort_by')
                    ->label('Sort By')
                    ->options([
                        'latest' => 'Latest',
                        'price_asc' => 'Price (Low to High)',
                        'price_desc' => 'Price (High to Low)',
                        'name_asc' => 'Name (A-Z)',
                    ])
                    ->default('latest'),
                Select::make('category_id')
                    ->label('Category')
                    ->options($productCategories)
                    ->nullable()
                    ->placeholder('All Categories'),
                Toggle::make('show_view_all')
                    ->label('Show View All Link')
                    ->default(true),
            ]);
    }

    private function latestPostsBlock(array $postCategories): Block
    {
        return Block::make('latest-posts')
            ->label('Latest Posts')
            ->icon('heroicon-o-newspaper')
            ->schema([
                ...self::sectionHeader(),
                TextInput::make('count')
                    ->label('Number of Posts')
                    ->numeric()
                    ->default(3),
                Select::make('sort_by')
                    ->label('Sort By')
                    ->options([
                        'latest' => 'Latest',
                        'oldest' => 'Oldest',
                    ])
                    ->default('latest'),
                Select::make('category_id')
                    ->label('Category')
                    ->options($postCategories)
                    ->nullable()
                    ->placeholder('All Categories'),
                Toggle::make('show_view_all')
                    ->label('Show View All Link')
                    ->default(true),
            ]);
    }

    private function ctaBlock(): Block
    {
        return Block::make('cta')
            ->label('CTA')
            ->icon('heroicon-o-rectangle-group')
            ->schema([
                TranslatableTabs::make('CTA Content')
                    ->schema([
                        TextInput::make('label')
                            ->label('Label'),
                        TextInput::make('title')
                            ->label('Title'),
                        TextInput::make('button_label')
                            ->label('Button Label'),
                    ]),
                TextInput::make('button_url')
                    ->label('Button URL')
                    ->default('#contact'),
            ]);
    }

    private function testimonialsBlock(): Block
    {
        return Block::make('testimonials')
            ->label('Testimonials')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->schema([
                ...self::sectionHeader(),
                Repeater::make('items')
                    ->label('Testimonials')
                    ->collapsible()
                    ->itemLabel(fn ($state) => $state['name'] ?? null)
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->label('Name')
                            ->required(),
                        TextInput::make('company')
                            ->columnSpanFull()
                            ->label('Company'),
                        Select::make('rating')
                            ->columnSpanFull()
                            ->label('Rating')
                            ->options(range(1, 5))
                            ->default(5),
                        TranslatableTabs::make()
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('role')
                                    ->label('Role')
                                    ->required(),
                                Textarea::make('quote')
                                    ->label('Quote')
                                    ->rows(3)
                                    ->required(),
                            ]),
                    ])
                    ->columns(2)
                    ->defaultItems(3)
                    ->addable(true)
                    ->reorderable(true),
            ]);
    }

    private function partnersBlock(): Block
    {
        return Block::make('partners')
            ->label('Partners')
            ->icon('heroicon-o-globe-alt')
            ->schema([
                ...self::sectionHeader(),
                Repeater::make('items')
                    ->label('Partners')
                    ->collapsible()
                    ->itemLabel(fn ($state) => $state['name'] ?? null)
                    ->schema([
                        Grid::make(3)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                TextInput::make('logo')
                                    ->label('Logo Path')
                                    ->helperText('Path relative to public/'),
                                TextInput::make('url')
                                    ->label('Website URL'),
                            ]),
                    ])
                    ->columns(2)
                    ->defaultItems(4)
                    ->addable(true)
                    ->reorderable(true),
            ]);
    }
}
