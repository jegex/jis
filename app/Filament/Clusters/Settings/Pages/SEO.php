<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Supports\SettingPage;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class SEO extends SettingPage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return 'SEO';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'seo';
    }

    public function getTitle(): string
    {
        return 'SEO';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('seo')
                    ->columnSpanFull()
                    ->persistTab()
                    ->key('seo')
                    ->statePath('seo')
                    ->tabs([
                        Tab::make('Homepage')
                            ->icon('heroicon-o-home')
                            ->columns(1)
                            ->schema([
                                Section::make('Default SEO')
                                    ->schema([
                                        TranslatableTabs::make('Default Meta')
                                            ->schema([
                                                TextInput::make('homepage_title')
                                                    ->label('Homepage Meta Title')
                                                    ->helperText('Variables: %site_title%, %site_description%'),
                                                Textarea::make('homepage_description')
                                                    ->label('Homepage Meta Description')
                                                    ->helperText('Variables: %site_title%, %site_description%'),
                                            ]),
                                    ]),
                                TextInput::make('homepage_image')
                                    ->label('Image Path')
                                    ->helperText('Path relative to public/'),
                            ]),
                        Tab::make('Posts')
                            ->icon('heroicon-o-newspaper')
                            ->columns(1)
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('post_title')
                                            ->label('Title Template')
                                            ->helperText('Variables: %title%, %excerpt%, %description%, %author%, %category%, %published_at%, %site_title%, %site_description%'),
                                        Textarea::make('post_description')
                                            ->label('Description Template')
                                            ->helperText('Variables: %title%, %excerpt%, %description%, %author%, %category%, %published_at%, %site_title%, %site_description%')
                                            ->rows(2),
                                    ]),
                                TextInput::make('post_image')
                                    ->label('Image Path')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Products')
                            ->icon('heroicon-o-shopping-bag')
                            ->columns(1)
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('product_title')
                                            ->label('Title Template')
                                            ->helperText('Variables: %title%, %short_description%, %description%, %category%, %site_title%, %site_description%'),
                                        Textarea::make('product_description')
                                            ->label('Description Template')
                                            ->helperText('Variables: %title%, %short_description%, %description%, %category%, %site_title%, %site_description%')
                                            ->rows(2),
                                    ]),
                                TextInput::make('product_image')
                                    ->label('Image Path')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Pages')
                            ->icon('heroicon-o-document')
                            ->columns(1)
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('page_title')
                                            ->label('Title Template')
                                            ->helperText('Variables: %title%, %description%, %site_title%, %site_description%'),
                                        Textarea::make('page_description')
                                            ->label('Description Template')
                                            ->helperText('Variables: %title%, %description%, %site_title%, %site_description%')
                                            ->rows(2),
                                    ]),
                                TextInput::make('page_image')
                                    ->label('Image Path')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Categories')
                            ->icon('heroicon-o-folder')
                            ->columns(1)
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('category_title')
                                            ->label('Title Template')
                                            ->helperText('Variables: %name%, %description%, %site_title%, %site_description%'),
                                        Textarea::make('category_description')
                                            ->label('Description Template')
                                            ->helperText('Variables: %name%, %description%, %site_title%, %site_description%')
                                            ->rows(2)->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function getSavedNotificationTitle(): ?string
    {
        return 'SEO settings saved successfully';
    }

    protected function globalTab(): Tab
    {
        return Tab::make('Global')
            ->icon('heroicon-o-globe-alt')
            ->schema([
                Section::make('Default SEO')
                    ->schema([
                        TranslatableTabs::make('Default Meta')
                            ->schema([
                                TextInput::make('frontend.default_meta_title')
                                    ->label('Default Meta Title'),
                                Textarea::make('frontend.default_meta_description')
                                    ->label('Default Meta Description')
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }
}
