<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionsResource\Pages;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shortcut resource: the Product resource pre-filtered to the Collections
 * subsidiary. Shares the Product model and ProductResource's form/table.
 */
class CollectionsResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Collections';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Collections product';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('subsidiary', 'collections')
            ->with('variants');
    }

    public static function form(Form $form): Form
    {
        return ProductResource::configureForm($form, 'collections');
    }

    public static function table(Table $table): Table
    {
        return ProductResource::configureTable($table, 'collections');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollections::route('/create'),
            'edit' => Pages\EditCollections::route('/{record}/edit'),
        ];
    }
}
