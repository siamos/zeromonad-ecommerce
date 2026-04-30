<?php

namespace App\Filament\Manager\Resources\Waitlists;

use App\Filament\Manager\Resources\Waitlists\Pages\ListWaitlists;
use App\Filament\Manager\Resources\Waitlists\Tables\WaitlistsTable;
use App\Models\Waitlist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WaitlistResource extends Resource
{
    protected static ?string $model = Waitlist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Orders & Customers';

    protected static ?int $navigationSort = 40;

    public static function table(Table $table): Table
    {
        return WaitlistsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaitlists::route('/'),
        ];
    }
}
