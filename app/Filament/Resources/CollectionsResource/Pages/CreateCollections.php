<?php

namespace App\Filament\Resources\CollectionsResource\Pages;

use App\Filament\Resources\CollectionsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollections extends CreateRecord
{
    protected static string $resource = CollectionsResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['subsidiary'] = 'collections';

        return $data;
    }
}
