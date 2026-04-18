<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public ?string $site_logo;
    public string $active_theme;
    public string $currency;
    public float $tax_rate;
    public int $low_stock_threshold;

    public static function group(): string
    {
        return 'general';
    }

    public static function defaults(): array
    {
        return [
            'site_name'           => 'ZeroMonad',
            'site_logo'           => null,
            'active_theme'        => 'Products',
            'currency'            => 'EUR',
            'tax_rate'            => 0.0,
            'low_stock_threshold' => 5,
        ];
    }
}
