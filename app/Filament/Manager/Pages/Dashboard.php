<?php

namespace App\Filament\Manager\Pages;

use App\Filament\Manager\Widgets\LowStockWidget;
use App\Filament\Manager\Widgets\ManagerStatsOverview;
use App\Filament\Manager\Widgets\PaymentMethodChart;
use App\Filament\Manager\Widgets\PendingOrdersWidget;
use App\Filament\Manager\Widgets\TopProductsWidget;
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
            ManagerStatsOverview::class,
            PendingOrdersWidget::class,
            LowStockWidget::class,
            TopProductsWidget::class,
            PaymentMethodChart::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
