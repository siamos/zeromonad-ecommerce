<?php

namespace App\Filament\Manager\Resources\ProductResource\Pages;

use App\Filament\Manager\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = $this->record->getTranslations('name');
        $data['description'] = $this->record->getTranslations('description');
        $data['short_description'] = $this->record->getTranslations('short_description');

        return $data;
    }
}
