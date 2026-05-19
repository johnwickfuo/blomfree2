<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'collections' => Tab::make('Collections')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('subsidiary', 'collections')),
            'gadgets' => Tab::make('Gadgets')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('subsidiary', 'gadgets')),
        ];
    }
}
