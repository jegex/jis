<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\ContentStatus;
use App\Filament\Schemas\Components\MyRichEditor;
use App\Filament\Schemas\Components\TitleWithSlug;
use App\Filament\Schemas\SeoSchema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                            Select::make('status')
                                ->options(ContentStatus::class)
                                ->default(ContentStatus::Draft->value)
                                ->live(),

                            DateTimePicker::make('scheduled_at')
                                ->label('Schedule Publish')
                                ->native(false)
                                ->timezone(config('app.timezone'))
                                ->visible(fn (Get $get): bool => $get('status') === ContentStatus::Future->value),
                        ]),

                    Section::make('SEO')
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema(fn (Schema $schema) => SeoSchema::configure($schema)),
                ])->columnSpan(1),
            ]);
    }
}
