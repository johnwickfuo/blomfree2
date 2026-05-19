<?php

namespace App\Filament\Resources\CollectionsResource\Pages;

use App\Filament\Resources\CollectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollections extends ListRecords
{
    protected static string $resource = CollectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
