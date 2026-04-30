<?php

namespace App\Filament\Manager\Resources\ReturnRequests\Pages;

use App\Filament\Manager\Resources\ReturnRequests\ReturnRequestResource;
use App\Notifications\ReturnRequestUpdated;
use Filament\Resources\Pages\EditRecord;

class EditReturnRequest extends EditRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        $returnRequest = $this->record->fresh(['order', 'user']);

        if ($returnRequest->isResolved()) {
            if (! $returnRequest->resolved_at) {
                $returnRequest->update(['resolved_at' => now()]);
            }

            $returnRequest->user->notify(new ReturnRequestUpdated($returnRequest));
        }
    }
}
