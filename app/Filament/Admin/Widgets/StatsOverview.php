<?php
namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '€' . number_format(Order::where('payment_status', 'paid')->sum('total'), 2))
                ->description('Paid orders')
                ->color('success'),
            Stat::make('Total Orders', Order::count())
                ->description('All time')
                ->color('primary'),
            Stat::make('Customers', User::role('customer')->count())
                ->description('Registered customers')
                ->color('info'),
            Stat::make('Products', Product::where('status', 'published')->count())
                ->description('Published products')
                ->color('warning'),
        ];
    }
}
