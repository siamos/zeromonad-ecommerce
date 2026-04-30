<?php

namespace App\Filament\Admin\Resources\ReturnRequests;

use App\Filament\Admin\Resources\ReturnRequests\Pages\EditReturnRequest;
use App\Filament\Admin\Resources\ReturnRequests\Pages\ListReturnRequests;
use App\Filament\Admin\Resources\ReturnRequests\Schemas\ReturnRequestForm;
use App\Filament\Admin\Resources\ReturnRequests\Tables\ReturnRequestsTable;
use App\Models\ReturnRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return ReturnRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturnRequests::route('/'),
            'edit' => EditReturnRequest::route('/{record}/edit'),
        ];
    }
}
