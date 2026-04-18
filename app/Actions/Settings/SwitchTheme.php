<?php

namespace App\Actions\Settings;

use App\Settings\GeneralSettings;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Activitylog\Facades\Activity;

class SwitchTheme
{
    use AsAction;

    public function handle(string $theme): void
    {
        $settings = app(GeneralSettings::class);
        $old      = $settings->active_theme;

        $settings->active_theme = $theme;
        $settings->save();

        activity('settings')
            ->withProperties(['from' => $old, 'to' => $theme])
            ->log("Theme switched to {$theme}");
    }
}
