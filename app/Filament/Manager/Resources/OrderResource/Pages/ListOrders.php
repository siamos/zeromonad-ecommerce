<?php
namespace App\Filament\Manager\Resources\OrderResource\Pages;
use App\Filament\Manager\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
