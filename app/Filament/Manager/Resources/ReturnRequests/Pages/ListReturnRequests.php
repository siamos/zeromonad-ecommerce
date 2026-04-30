<?php

namespace App\Filament\Manager\Resources\ReturnRequests\Pages;

use App\Filament\Manager\Resources\ReturnRequests\ReturnRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListReturnRequests extends ListRecords
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
