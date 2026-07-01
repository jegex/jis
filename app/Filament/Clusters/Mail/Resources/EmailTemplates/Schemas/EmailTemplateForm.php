<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Resources\EmailTemplates\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\EmailTemplateType;
use Awcodes\RicherEditor\Plugins\LinkPlugin;
use Awcodes\RicherEditor\Plugins\SourceCodePlugin;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Wrteam\FilamentCkeditorField\CKEditor;

final class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Select::make('type')
                            ->options(EmailTemplateType::class)
                            ->required()
                            ->live(),

                        Toggle::make('is_active')
                            ->default(true),

                        TranslatableTabs::make()
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('subject')
                                    ->required()
                                    ->columnSpanFull()
                                    ->maxLength(255),

                                RichEditor::make('body')
                                    ->plugins([
                                        SourceCodePlugin::make(),
                                        LinkPlugin::make(),
                                    ])
                                    ->enableToolbarButtons(['sourceCode', 'link'])
                                    ->disableToolbarButtons(['attachFiles'])
                                    ->required()
                                    ->columnSpanFull()
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 200px;',
                                    ])
                                    ->helperText('Supports HTML. Available variables: {name}, {email}, {order_number}, {download_url}, {total}'),
                            ]),
                    ]),
            ]);
    }
}
