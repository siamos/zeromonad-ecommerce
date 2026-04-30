<?php

namespace App\Filament\Admin\Resources\Waitlists\Pages;

use App\Filament\Admin\Resources\Waitlists\WaitlistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaitlist extends CreateRecord
{
    protected static string $resource = WaitlistResource::class;
}
