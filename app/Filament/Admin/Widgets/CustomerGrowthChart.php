<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CustomerGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Customer Growth';

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = collect(range(5, 0))->map(function ($month) {
            $date = Carbon::now()->subMonths($month);

            return User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $data->values()->toArray(),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.1)',
                ],
            ],
            'labels' => collect(range(5, 0))->map(fn ($m) => Carbon::now()->subMonths($m)->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
