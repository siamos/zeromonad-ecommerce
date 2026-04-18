<?php
namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?string $heading = 'Latest Orders';
    protected static ?int $sort = 7;
    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->default('Guest'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'pending', 'success' => 'paid', 'primary' => 'processing']),
                Tables\Columns\TextColumn::make('total')->money('EUR'),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ]);
    }
}
