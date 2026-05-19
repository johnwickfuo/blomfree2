<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GadgetsResource\Pages;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shortcut resource: the Product resource pre-filtered to the Gadgets
 * subsidiary. Shares the Product model and ProductResource's form/table.
 */
class GadgetsResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Gadgets';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Gadgets product';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('subsidiary', 'gadgets')
            ->with('variants');
    }

    public static function form(Form $form): Form
    {
        return ProductResource::configureForm($form, 'gadgets');
    }

    public static function table(Table $table): Table
    {
        return ProductResource::configureTable($table, 'gadgets');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGadgets::route('/'),
            'create' => Pages\CreateGadgets::route('/create'),
            'edit' => Pages\EditGadgets::route('/{record}/edit'),
        ];
    }
}
