<?php

namespace App\Filament\Resources\GadgetsResource\Pages;

use App\Filament\Resources\GadgetsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGadgets extends CreateRecord
{
    protected static string $resource = GadgetsResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['subsidiary'] = 'gadgets';

        return $data;
    }
}
