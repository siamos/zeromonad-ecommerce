<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Product;
use App\Models\Vehicle;
use App\Settings\GeneralSettings;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $theme = app(GeneralSettings::class)->active_theme;

        $query = match ($theme) {
            'Activities' => Activity::withCount('orderItems')->orderByDesc('order_items_count')->limit(5),
            'Bookings' => Accommodation::withCount('orderItems')->orderByDesc('order_items_count')->limit(5),
            'Cars' => Vehicle::withCount('orderItems')->orderByDesc('order_items_count')->limit(5),
            default => Product::withCount('orderItems')->orderByDesc('order_items_count')->limit(5),
        };

        $priceLabel = match ($theme) {
            'Bookings' => 'Price/Night',
            'Cars' => 'Price/Day',
            default => 'Price',
        };

        $heading = match ($theme) {
            'Activities' => 'Top Activities (All Time)',
            'Bookings' => 'Top Accommodations (All Time)',
            'Cars' => 'Top Vehicles (All Time)',
            default => 'Top Products (All Time)',
        };

        $this->heading = $heading;

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(30),
                Tables\Columns\TextColumn::make('order_items_count')->label('Sales')->sortable(),
                Tables\Columns\TextColumn::make('price_display')
                    ->label($priceLabel)
                    ->state(fn ($record) => '€'.number_format(
                        $record->price ?? $record->price_per_night ?? $record->price_per_day ?? 0, 2
                    )),
            ]);
    }
}
