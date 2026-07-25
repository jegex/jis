<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\Components\TitleWithSlug;
use App\Filament\Schemas\SeoSchema;
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
                Section::make([
                    TitleWithSlug::make(),
                    MyRichEditor::make('content'),
                ])->columnSpan(2),

                Group::make([
                    Section::make('Publishing')
                        ->collapsible()
                        ->visible(fn (): bool => auth()->user()?->can('Publish:Page') ?? false)
                        ->schema([
                            Toggle::make('is_published')
                                ->default(false),
                        ]),

                    Section::make('SEO')
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
                ])->columnSpan(1),
            ]);
    }
}
