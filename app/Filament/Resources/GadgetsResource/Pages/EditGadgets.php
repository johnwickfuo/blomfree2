<?php

namespace App\Filament\Resources\GadgetsResource\Pages;

use App\Filament\Resources\GadgetsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGadgets extends EditRecord
{
    protected static string $resource = GadgetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
