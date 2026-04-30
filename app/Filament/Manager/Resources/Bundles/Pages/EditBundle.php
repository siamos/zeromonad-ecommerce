<?php

namespace App\Filament\Manager\Resources\Bundles\Pages;

use App\Filament\Manager\Resources\Bundles\BundleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBundle extends EditRecord
{
    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
