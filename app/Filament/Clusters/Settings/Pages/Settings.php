<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Supports\SettingPage;
use App\Models\Language;
use BackedEnum;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class Settings extends SettingPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = -1;

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('settings')
                    ->columnSpanFull()
                    ->persistTab()
                    ->id('settings')
                    ->tabs([
                        Tab::make('General')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('site_title')
                                            ->maxLength(255),
                                        Textarea::make('site_description')
                                            ->maxLength(65535),
                                    ]),

                                FileUpload::make('favicon')
                                    ->disk('public')
                                    ->preserveFilenames()
                                    ->image()
                                    ->avatar()
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('logo_dark')
                                            ->disk('public')
                                            ->preserveFilenames()
                                            ->image(),
                                        FileUpload::make('logo_light')
                                            ->disk('public')
                                            ->preserveFilenames()
                                            ->image(),
                                    ]),

                                Section::make('Date & Time')
                                    ->columns()
                                    ->collapsible()
                                    ->schema([
                                        Radio::make('date_format')
                                            ->required()
                                            ->options([
                                                'F j, Y' => 'F j, Y',
                                                'Y-m-d' => 'Y-m-d',
                                                'm/d/Y' => 'm/d/Y',
                                                'd/m/Y' => 'd/m/Y',
                                                'd.m.Y' => 'd.m.Y',
                                            ])
                                            ->descriptions([
                                                'F j, Y' => now()->format('F j, Y'),
                                                'Y-m-d' => now()->format('Y-m-d'),
                                                'm/d/Y' => now()->format('m/d/Y'),
                                                'd/m/Y' => now()->format('d/m/Y'),
                                                'd.m.Y' => now()->format('d.m.Y'),
                                            ]),

                                        Radio::make('time_format')
                                            ->required()
                                            ->options([
                                                'g:i a' => 'g:i a',
                                                'g:i A' => 'g:i A',
                                                'H:i' => 'H:i',
                                            ])
                                            ->descriptions([
                                                'g:i a' => now()->format('g:i a'),
                                                'g:i A' => now()->format('g:i A'),
                                                'H:i' => now()->format('H:i'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Collection')
                            ->icon(Heroicon::OutlinedSquare3Stack3d)
                            ->schema([
                                TextInput::make('products_per_page')
                                    ->label('Products per page')
                                    ->helperText('Number of products per page')
                                    ->required()
                                    ->numeric()
                                    ->default(9),
                                TextInput::make('posts_per_page')
                                    ->label('Posts per page')
                                    ->helperText('Number of posts per page')
                                    ->required()
                                    ->numeric()
                                    ->default(9),
                            ]),

                        Tab::make('Localization')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema([
                                Select::make('supported_locales')
                                    ->label('Supported Locales')
                                    ->live()
                                    ->options(Language::all()->pluck('name', 'code'))
                                    ->required()
                                    ->multiple(),
                                Select::make('default_locale')
                                    ->label('Default Locale')
                                    ->options(fn (Get $get) => Language::query()
                                        ->whereIn('code', $get('supported_locales'))
                                        ->pluck('name', 'code'))
                                    ->required(),
                                Toggle::make('hide_default_locale')
                                    ->label('Hide Default Locale')
                                    ->helperText('Default \'true\'')
                                    ->default(true),
                                Toggle::make('redirect_enabled')
                                    ->label('Redirect Enabled')
                                    ->helperText('Default \'true\'')
                                    ->default(true),
                                Toggle::make('persist_locale.session')
                                    ->label('Persist Locale Session')
                                    ->helperText('Default \'true\'')
                                    ->default(true),
                                Toggle::make('persist_locale.cookie')
                                    ->label('Persist Locale Cookie')
                                    ->helperText('Default \'true\'')
                                    ->default(true),
                            ]),

                        Tab::make('Footer')
                            ->icon(Heroicon::OutlinedDocumentArrowDown)
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        Textarea::make('footer_description')
                                            ->label('Footer Description')
                                            ->rows(3),
                                        TextInput::make('footer_copyright')
                                            ->label('Copyright Text')
                                            ->helperText('Use :year for dynamic year'),
                                    ]),

                                Fieldset::make('Address & Contact Information')
                                    ->schema([
                                        Textarea::make('contact_address')
                                            ->label('Address')
                                            ->columnSpanFull(),
                                        TextInput::make('contact_phone')
                                            ->label('Phone')
                                            ->tel(),
                                        TextInput::make('contact_email')
                                            ->label('Email')
                                            ->email(),
                                    ]),

                                Repeater::make('social')
                                    ->itemLabel(fn ($state) => $state['name'] ?? null)
                                    ->collapsed()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Name'),
                                        TextInput::make('url')
                                            ->label('URL'),
                                        CodeEditor::make('icon_svg')
                                            ->language(CodeEditor\Enums\Language::Html),
                                    ]),
                            ]),

                        Tab::make('Custom Scripts')
                            ->icon(Heroicon::OutlinedCodeBracket)
                            ->schema([
                                CodeEditor::make('before_head')
                                    ->language(CodeEditor\Enums\Language::Html)
                                    ->helperText('Put scripts before </head> tag'),
                                CodeEditor::make('before_body')
                                    ->language(CodeEditor\Enums\Language::Html)
                                    ->helperText('Put scripts before </body> tag'),
                            ]),
                    ]),
            ]);
    }
}
