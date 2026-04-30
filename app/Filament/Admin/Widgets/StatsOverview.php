<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\Settings\GeneralSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $theme = app(GeneralSettings::class)->active_theme;

        [$itemCount, $itemLabel] = match ($theme) {
            'Activities' => [Activity::where('status', 'published')->count(), 'Activities'],
            'Bookings' => [Accommodation::where('status', 'published')->count(), 'Accommodations'],
            'Cars' => [Vehicle::where('status', 'published')->count(), 'Vehicles'],
            default => [Product::where('status', 'published')->count(), 'Products'],
        };

        $avgOrderValue = Order::where('payment_status', 'paid')->avg('total') ?? 0;

        return [
            Stat::make('Total Revenue', '€'.number_format(Order::where('payment_status', 'paid')->sum('total'), 2))
                ->description('Paid orders')
                ->color('success'),
            Stat::make('Total Orders', Order::count())
                ->description('All time')
                ->color('primary'),
            Stat::make('Avg Order Value', '€'.number_format($avgOrderValue, 2))
                ->description('Paid orders average')
                ->color('warning'),
            Stat::make('Customers', User::role('customer')->count())
                ->description('Registered customers')
                ->color('info'),
            Stat::make($itemLabel, $itemCount)
                ->description('Published '.$itemLabel)
                ->color('gray'),
        ];
    }
}
