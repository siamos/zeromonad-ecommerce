<?php
namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 8;
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $dbOk = true;
        try { DB::connection()->getPdo(); } catch (\Exception $e) { $dbOk = false; }

        $cacheOk = true;
        try { Cache::set('health_check', true, 5); } catch (\Exception $e) { $cacheOk = false; }

        return [
            Stat::make('Database', $dbOk ? 'Connected' : 'Error')->color($dbOk ? 'success' : 'danger'),
            Stat::make('Cache', $cacheOk ? 'Working' : 'Error')->color($cacheOk ? 'success' : 'danger'),
            Stat::make('PHP Version', PHP_VERSION)->color('primary'),
        ];
    }
}
