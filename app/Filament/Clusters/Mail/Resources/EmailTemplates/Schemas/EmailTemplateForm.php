<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Resources\EmailTemplates\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\EmailTemplateType;
use Awcodes\RicherEditor\Plugins\LinkPlugin;
use Awcodes\RicherEditor\Plugins\SourceCodePlugin;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                            ->required()
                            ->live()
                            ->options(EmailTemplateType::class),

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
                            ]),

                        Text::make(fn (Get $get) => view('filament.info-variable-email', ['type' => $get('type') ?? null]))
                    ]),
            ]);
    }
}
