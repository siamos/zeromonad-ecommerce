<?php
namespace App\Filament\Manager\Widgets;

use App\Models\Product;
use App\Settings\GeneralSettings;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Low Stock';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        $threshold = app(GeneralSettings::class)->low_stock_threshold;

        return $table
            ->query(Product::where('stock', '<=', $threshold)->where('status', 'published'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(30),
                Tables\Columns\TextColumn::make('sku'),
                Tables\Columns\TextColumn::make('stock')->color('danger'),
            ]);
    }
}
