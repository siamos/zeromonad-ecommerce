<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueByPeriodWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue by Period';

    protected static ?int $sort = 10;

    protected ?string $pollingInterval = '60s';

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Last 30 days',
            'week' => 'Last 12 weeks',
            'month' => 'Last 6 months',
            'year' => 'Last 5 years',
        ];
    }

    protected function getData(): array
    {
        [$labels, $data] = match ($this->filter ?? 'month') {
            'day' => $this->byDay(),
            'week' => $this->byWeek(),
            'year' => $this->byYear(),
            default => $this->byMonth(),
        };

        return [
            'datasets' => [[
                'label' => 'Revenue (€)',
                'data' => $data,
                'borderColor' => '#4f46e5',
                'backgroundColor' => 'rgba(79,70,229,0.1)',
                'fill' => true,
                'tension' => 0.4,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function byDay(): array
    {
        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = (float) Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total');
        }

        return [$labels, $data];
    }

    private function byWeek(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $labels[] = $start->format('d M');
            $data[] = (float) Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total');
        }

        return [$labels, $data];
    }

    private function byMonth(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $data[] = (float) Order::where('payment_status', 'paid')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total');
        }

        return [$labels, $data];
    }

    private function byYear(): array
    {
        $labels = [];
        $data = [];

        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $labels[] = (string) $year;
            $data[] = (float) Order::where('payment_status', 'paid')
                ->whereYear('created_at', $year)
                ->sum('total');
        }

        return [$labels, $data];
    }
}
