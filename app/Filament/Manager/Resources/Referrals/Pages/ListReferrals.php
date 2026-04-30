<?php

namespace App\Filament\Manager\Resources\Referrals\Pages;

use App\Filament\Manager\Resources\Referrals\ReferralResource;
use Filament\Resources\Pages\ListRecords;

class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
