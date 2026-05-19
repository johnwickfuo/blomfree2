<?php

namespace App\Filament\Resources\InstallmentRefundResource\Pages;

use App\Filament\Resources\InstallmentRefundResource;
use Filament\Resources\Pages\ListRecords;

class ListInstallmentRefunds extends ListRecords
{
    protected static string $resource = InstallmentRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
