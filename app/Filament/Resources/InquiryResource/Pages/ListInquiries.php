<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListInquiries extends ListRecords
{
    protected static string $resource = InquiryResource::class;

    // Inquiries arrive via the public contact form, never created in admin.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
