<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Orders by Status';

    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $statuses = ['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded'];

        $counts = collect($statuses)->map(fn ($s) => Order::where('status', $s)->count());

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $counts->values()->toArray(),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#6366f1',
                        '#10b981',
                        '#3b82f6',
                        '#22c55e',
                        '#ef4444',
                        '#9ca3af',
                    ],
                ],
            ],
            'labels' => collect($statuses)->map(fn ($s) => ucfirst($s))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
