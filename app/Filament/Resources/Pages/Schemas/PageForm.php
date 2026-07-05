<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\SeoSchema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PageForm
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
                            MyRichEditor::make('content'),
                        ]),
                ])->columnSpan(2),

                Group::make([
                    Section::make('Publishing')
                        ->collapsible()
                        ->schema([
                            Toggle::make('is_published')
                                ->default(false),
                        ]),
                ])->columnSpan(1),

                Section::make('SEO')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
            ]);
    }
}
