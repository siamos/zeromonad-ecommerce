<?php
namespace App\Filament\Manager\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Orders';
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::where('status', 'pending')->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number'),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->default('Guest'),
                Tables\Columns\TextColumn::make('total')->money('EUR'),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ]);
    }
}
