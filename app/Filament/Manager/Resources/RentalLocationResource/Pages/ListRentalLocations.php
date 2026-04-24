<?php

namespace App\Filament\Manager\Resources\RentalLocationResource\Pages;

use App\Filament\Manager\Resources\RentalLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalLocations extends ListRecords
{
    protected static string $resource = RentalLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
