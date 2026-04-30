<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vehicle;
use App\Settings\GeneralSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ManagerStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $settings = app(GeneralSettings::class);
        $theme = $settings->active_theme;

        $thirdStat = match ($theme) {
            'Activities' => Stat::make('Activities', Activity::where('status', 'published')->count())
                ->description('Published activities')
                ->color('info'),
            'Bookings' => Stat::make('Accommodations', Accommodation::where('status', 'published')->count())
                ->description('Published accommodations')
                ->color('info'),
            'Cars' => Stat::make('Vehicles', Vehicle::where('status', 'published')->count())
                ->description('Published vehicles')
                ->color('info'),
            default => Stat::make('Low Stock', Product::where('stock', '<=', $settings->low_stock_threshold)->count())
                ->description('Products below threshold')
                ->color('danger'),
        };

        return [
            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->color('warning'),
            Stat::make("Today's Revenue", '€'.number_format(
                Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total'), 2
            ))->color('success'),
            $thirdStat,
        ];
    }
}
