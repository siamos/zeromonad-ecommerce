<?php

namespace App\Filament\Manager\Resources\AccommodationResource\Pages;

use App\Filament\Manager\Resources\AccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccommodations extends ListRecords
{
    protected static string $resource = AccommodationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
