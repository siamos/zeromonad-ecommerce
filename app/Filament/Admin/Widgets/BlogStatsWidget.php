<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BlogPost;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            Stat::make('Blog Posts', BlogPost::count())
                ->description(BlogPost::where('status', 'published')->count().' published')
                ->color('primary'),
            Stat::make('AI Generated', BlogPost::where('ai_generated', true)->count())
                ->description('Auto-generated posts')
                ->color('warning'),
        ];
    }
}
