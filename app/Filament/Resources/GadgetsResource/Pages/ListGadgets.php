<?php

namespace App\Filament\Resources\GadgetsResource\Pages;

use App\Filament\Resources\GadgetsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGadgets extends ListRecords
{
    protected static string $resource = GadgetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
