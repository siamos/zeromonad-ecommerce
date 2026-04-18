<?php
namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue';
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = collect(range(5, 0))->map(function ($month) {
            $date = Carbon::now()->subMonths($month);
            return Order::where('payment_status', 'paid')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total');
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (€)',
                    'data'            => $data->values()->toArray(),
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                ],
            ],
            'labels' => collect(range(5, 0))->map(fn ($m) => Carbon::now()->subMonths($m)->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string { return 'line'; }
}
