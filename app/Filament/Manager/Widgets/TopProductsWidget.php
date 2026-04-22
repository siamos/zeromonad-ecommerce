<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Products (This Month)';

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::withCount(['orderItems' => fn ($q) => $q->whereMonth('created_at', now()->month)])
                    ->orderByDesc('order_items_count')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(30),
                Tables\Columns\TextColumn::make('order_items_count')->label('Sales This Month'),
                Tables\Columns\TextColumn::make('stock'),
            ]);
    }
}
