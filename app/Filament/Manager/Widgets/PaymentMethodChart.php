<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class PaymentMethodChart extends ChartWidget
{
    protected ?string $heading = 'Payment Methods (This Month)';

    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $methods = Order::whereMonth('created_at', now()->month)
            ->selectRaw('payment_method, count(*) as count')
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method');

        return [
            'datasets' => [
                [
                    'data' => $methods->values()->toArray(),
                    'backgroundColor' => ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                ],
            ],
            'labels' => $methods->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
