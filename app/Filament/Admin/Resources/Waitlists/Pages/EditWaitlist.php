<?php

namespace App\Filament\Admin\Resources\Waitlists\Pages;

use App\Filament\Admin\Resources\Waitlists\WaitlistResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaitlist extends EditRecord
{
    protected static string $resource = WaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
