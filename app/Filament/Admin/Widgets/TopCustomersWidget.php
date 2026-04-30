<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopCustomersWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Customers';

    protected static ?int $sort = 11;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->select('user_id')
                    ->selectRaw('COUNT(*) as order_count')
                    ->selectRaw('SUM(total) as total_spent')
                    ->where('payment_status', 'paid')
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->orderByDesc('total_spent')
                    ->limit(10)
                    ->with('user')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('order_count')
                    ->label('Orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->formatStateUsing(fn ($state) => '€'.number_format($state, 2))
                    ->sortable(),
            ]);
    }
}
