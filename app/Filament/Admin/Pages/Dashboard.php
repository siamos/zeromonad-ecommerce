<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\BlogStatsWidget;
use App\Filament\Admin\Widgets\CustomerGrowthChart;
use App\Filament\Admin\Widgets\LatestOrders;
use App\Filament\Admin\Widgets\OrdersByStatusChart;
use App\Filament\Admin\Widgets\PaymentMethodChart;
use App\Filament\Admin\Widgets\RevenueByPeriodWidget;
use App\Filament\Admin\Widgets\RevenueChart;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\SystemHealthWidget;
use App\Filament\Admin\Widgets\TopCustomersWidget;
use App\Filament\Admin\Widgets\TopProductsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-home';
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            RevenueChart::class,
            CustomerGrowthChart::class,
            TopProductsWidget::class,
            PaymentMethodChart::class,
            OrdersByStatusChart::class,
            BlogStatsWidget::class,
            LatestOrders::class,
            SystemHealthWidget::class,
            RevenueByPeriodWidget::class,
            TopCustomersWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
