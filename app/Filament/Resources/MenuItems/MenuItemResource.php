<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems;

use App\Filament\Resources\MenuItems\Pages\ManageMenuItems;
use App\Models\MenuItem as MenuItemModel;
use BackedEnum;
use Biostate\FilamentMenuBuilder\Enums\MenuItemTarget;
use Biostate\FilamentMenuBuilder\Enums\MenuItemType;
use Biostate\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Route;

final class MenuItemResource extends \Biostate\FilamentMenuBuilder\Filament\Resources\MenuItemResource
{
    protected static ?string $model = MenuItemModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function getFormSchemaArray(): array
    {
        return self::getFormSchema();
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('filament-menu-builder::menu-builder.form_labels.name'))
                ->autofocus()
                ->required()
                ->maxLength(255)
                ->translatableTabs(),
            Select::make('menu_id')
                ->label(__('filament-menu-builder::menu-builder.menu_name'))
                ->options(fn () => FilamentMenuBuilderPlugin::get()->getMenuModel()::all()->pluck('name', 'id'))
                ->hidden(fn ($context) => ! in_array($context, ['edit', 'create']))
                ->required(),
            Select::make('target')
                ->label(__('filament-menu-builder::menu-builder.form_labels.target'))
                ->options(MenuItemTarget::class)
                ->default('_self')
                ->required(),
            TextInput::make('link_class')
                ->label(__('filament-menu-builder::menu-builder.form_labels.link_class'))
                ->maxLength(255),
            TextInput::make('wrapper_class')
                ->label(__('filament-menu-builder::menu-builder.form_labels.wrapper_class'))
                ->maxLength(255),
            Fieldset::make('URL')
                ->columns(1)
                ->schema([
                    Select::make('type')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.type'))
                        ->options(MenuItemType::class)
                        ->afterStateUpdated(function (callable $set) {
                            $set('menuable_type', null);
                            $set('menuable_id', null);
                            $set('url', null);
                        })
                        ->default('link')
                        ->required()
                        ->live(),
                    // URL
                    TextInput::make('url')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.url'))
                        ->hidden(fn ($get) => $get('type')?->value !== 'link')
                        ->maxLength(255)
                        ->required(fn ($get) => $get('type')?->value === 'link'),
                    // ROUTE
                    Select::make('route')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.route'))
                        ->searchable()
                        ->helperText(__('filament-menu-builder::menu-builder.route_helper_text'))
                        ->options(
                            function () {
                                $excludedRoutes = config('filament-menu-builder.exclude_route_names', []);

                                $routes = collect(Route::getRoutes())
                                    ->filter(function ($route) use ($excludedRoutes) {
                                        $routeName = $route->getName();
                                        if (! $routeName) {
                                            return false;
                                        }

                                        foreach ($excludedRoutes as $pattern) {
                                            if (preg_match($pattern, $routeName)) {
                                                return false;
                                            }
                                        }

                                        return true;
                                    })
                                    ->map(function ($route) {
                                        $name = $route->getName();

                                        $cleaned = preg_replace('/^(?:translated_\w{2}\.|without_locale\.)/', '', $name);

                                        return $cleaned !== $name
                                            ? ['base' => $cleaned, 'original' => $name]
                                            : ['base' => $name, 'original' => $name];
                                    })
                                    ->unique('base')
                                    ->sortBy('base')
                                    ->mapWithKeys(fn ($item) => [$item['base'] => $item['base']]);

                                return $routes;
                            }
                        )
                        ->hidden(fn ($get) => $get('type')?->value !== 'route')
                        ->required(fn ($get) => $get('type')?->value === 'route')
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            if ($state === null) {
                                return [];
                            }

                            $lookup = MenuItemModel::resolveLookupRouteName($state);
                            $route = app('router')->getRoutes()->getByName($lookup);
                            if (! $route) {
                                return [];
                            }

                            $uri = $route->uri();

                            preg_match_all('/\{(\w+?)\}/', $uri, $matches);
                            $parameters = $matches[1];

                            if (empty($parameters)) {
                                return [];
                            }

                            $set('route_parameters', array_fill_keys($parameters, null));
                        })
                        ->live(),
                    KeyValue::make('route_parameters')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.route_parameters'))
                        ->hidden(fn ($get) => $get('type')?->value !== 'route')
                        ->helperText(function ($get, $set, $operation) {
                            if ($get('route') === null) {
                                return __('filament-menu-builder::menu-builder.route_parameters_empty_helper_text');
                            }
                            $lookup = MenuItemModel::resolveLookupRouteName($get('route'));
                            $route = app('router')->getRoutes()->getByName($lookup);
                            if (! $route) {
                                return __('filament-menu-builder::menu-builder.route_parameters_not_found_helper_text');
                            }

                            $uri = $route->uri();

                            preg_match_all('/\{(\w+?)\}/', $uri, $matches);
                            $parameters = $matches[1];

                            if (empty($parameters)) {
                                return __('filament-menu-builder::menu-builder.route_parameters_no_parameters_helper_text');
                            }

                            return __('filament-menu-builder::menu-builder.route_parameters_has_parameters_helper_text', [
                                'parameters' => implode(', ', $parameters),
                            ]);
                        }),
                    // MODEL
                    Select::make('menuable_type')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.menuable_type'))
                        ->options(
                            array_flip(config('filament-menu-builder.models', []))
                        )
                        ->live()
                        ->required(fn ($get) => $get('type')->value === 'model')
                        ->afterStateUpdated(fn (callable $set) => $set('menuable_id', null))
                        ->hidden(fn ($get) => empty(config('filament-menu-builder.models', [])) || $get('type')->value !== 'model'),
                    Select::make('menuable_id')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.menuable_id'))
                        ->searchable()
                        ->options(fn ($get) => $get('menuable_type')::all()->mapWithKeys(fn ($model) => [$model->getKey() => $model->getFilamentSearchOptionName()]))
                        ->getSearchResultsUsing(function (string $search, callable $get) {
                            $className = $get('menuable_type');

                            return $className::filamentSearch($search)->get()->mapWithKeys(fn ($model) => [$model->getKey() => $model->getFilamentSearchOptionName()]);
                        })
                        ->required(fn ($get) => $get('menuable_type') !== null)
                        ->getOptionLabelUsing(fn ($value, $get): ?string => $get('menuable_type')::find($value)?->getFilamentSearchOptionName())
                        ->hidden(fn ($get) => $get('menuable_type') === null),
                    Toggle::make('use_menuable_name')
                        ->label(__('filament-menu-builder::menu-builder.form_labels.use_menuable_name'))
                        ->hidden(fn ($get) => $get('menuable_type') === null)
                        ->default(false),
                ]),
            KeyValue::make('parameters')
                ->label(__('filament-menu-builder::menu-builder.form_labels.parameters'))
                ->helperText(__('filament-menu-builder::menu-builder.parameters_helper_text', [
                    'parameters' => implode(', ', config('filament-menu-builder.usable_parameters', [])),
                ])),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-menu-builder::menu-builder.form_labels.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('url')
                    ->label(__('filament-menu-builder::menu-builder.form_labels.url')),
                TextColumn::make('menu.name')
                    ->label(__('filament-menu-builder::menu-builder.menu_name')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => ManageMenuItems::route('/'),
    //     ];
    // }
}
