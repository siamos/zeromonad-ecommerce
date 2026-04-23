<?php

namespace App\Actions\Settings;

use App\Settings\GeneralSettings;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Activitylog\Facades\Activity;
use App\Enums\Theme;

class SwitchTheme
{
    use AsAction;

    public function handle(Theme $theme): void
    {
        $settings = app(GeneralSettings::class);
        $old      = $settings->active_theme;

        $settings->active_theme = $theme->value;
        $settings->save();

        activity('settings')
            ->withProperties(['from' => $old, 'to' => $theme])
            ->log("Theme switched to {$theme->value}");
    }
}
