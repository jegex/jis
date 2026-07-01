<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategories;

use App\Enums\CategoryType;
use App\Filament\Resources\ProductCategories\Pages\CreateProductCategory;
use App\Filament\Resources\ProductCategories\Pages\EditProductCategory;
use App\Filament\Resources\ProductCategories\Pages\ListProductCategories;
use App\Filament\Resources\ProductCategories\RelationManagers\ProductsRelationManager;
use App\Filament\Schemas\CategoryForm;
use App\Filament\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'product-categories';

    public static function getType(): CategoryType
    {
        return CategoryType::Product;
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table, 'products');
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductCategories::route('/'),
            'create' => CreateProductCategory::route('/create'),
            'edit' => EditProductCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', self::getType()->value);
    }
}
