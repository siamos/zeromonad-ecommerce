<?php

namespace App\Filament\Admin\Resources\Waitlists\Pages;

use App\Filament\Admin\Resources\Waitlists\WaitlistResource;
use Filament\Resources\Pages\ListRecords;

class ListWaitlists extends ListRecords
{
    protected static string $resource = WaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
