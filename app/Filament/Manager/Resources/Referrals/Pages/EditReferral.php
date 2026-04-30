<?php

namespace App\Filament\Manager\Resources\Referrals\Pages;

use App\Filament\Manager\Resources\Referrals\ReferralResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferral extends EditRecord
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
