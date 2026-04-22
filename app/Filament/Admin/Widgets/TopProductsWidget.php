<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Products (All Time)';

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::withCount('orderItems')
                    ->orderByDesc('order_items_count')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(30),
                Tables\Columns\TextColumn::make('order_items_count')->label('Sales')->sortable(),
                Tables\Columns\TextColumn::make('price')->money('EUR'),
            ]);
    }
}
