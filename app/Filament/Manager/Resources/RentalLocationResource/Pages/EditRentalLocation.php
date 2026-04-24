<?php

namespace App\Filament\Manager\Resources\RentalLocationResource\Pages;

use App\Filament\Manager\Resources\RentalLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalLocation extends EditRecord
{
    protected static string $resource = RentalLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
