<?php

declare(strict_types=1);

namespace App\Filament\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(1)
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        TranslatableTabs::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (blank($get('slug'))) {
                                            $set('slug', str($state)->slug());
                                        }
                                    }),

                                Textarea::make('description')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ]),
                        TextInput::make('slug')
                            ->required()
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule, $livewire) => $rule->where('type', $livewire->getResource()::getType()->value)
                            )
                            ->maxLength(255),

                        Toggle::make('is_published')
                            ->default(true),
                    ]),

                Section::make('SEO')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
            ]);
    }
}
