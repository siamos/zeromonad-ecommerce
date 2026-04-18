<?php
namespace App\Filament\Manager\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ManagerStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->color('warning'),
            Stat::make("Today's Revenue", '€' . number_format(
                Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total'), 2
            ))->color('success'),
            Stat::make('Low Stock', Product::whereColumn('stock', '<=', \DB::raw(
                '(SELECT low_stock_threshold FROM settings WHERE key = "low_stock_threshold" LIMIT 1)'
            ))->count())->color('danger'),
        ];
    }
}
