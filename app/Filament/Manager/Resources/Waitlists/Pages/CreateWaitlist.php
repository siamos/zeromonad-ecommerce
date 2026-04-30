<?php

namespace App\Filament\Manager\Resources\Waitlists\Pages;

use App\Filament\Manager\Resources\Waitlists\WaitlistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaitlist extends CreateRecord
{
    protected static string $resource = WaitlistResource::class;
}
