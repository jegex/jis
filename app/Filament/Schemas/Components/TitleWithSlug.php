<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use App\Actions\GenerateSlug;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

final class TitleWithSlug
{
    public static function make()
    {
        return Group::make([
            TextInput::make('title')
                ->autofocus()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Get $get, Set $set, $model, $livewire, $record) {
                    if (! empty($get('slug'))) {
                        return;
                    }
                    $slug = GenerateSlug::run($state, $model, $livewire->activeLocale, $record?->id);
                    $set('slug', $slug);
                })
                ->maxLength(255)
                ->belowContent([
                    Text::make(fn (Get $get) => 'Permalink: /'.$get('../slug')),
                    Action::make('editSlug')
                        ->iconButton()
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->modalWidth('lg')
                        ->schema(fn (Get $get) => [
                            TextInput::make('slug')
                                ->required()
                                ->default(fn () => $get('slug'))
                                ->maxLength(255),
                        ])
                        ->action(function (Set $set, $data, $model, $livewire, $record) {
                            $slug = $data['slug'];
                            if (empty($slug)) {
                                return;
                            }
                            $slug = GenerateSlug::run($slug, $model, $livewire->activeLocale, $record?->id);
                            $set('slug', $slug);
                        }),
                ]),
            Hidden::make('slug')
                ->required()
                ->unique(ignoreRecord: true),
        ]);
    }
}
