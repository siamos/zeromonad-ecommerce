<?php

namespace App\Settings;

use App\Enums\Theme;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public ?string $site_logo;

    public string $active_theme;

    public string $currency;

    public float $tax_rate;

    public int $low_stock_threshold;

    public ?string $site_description;

    public ?string $hero_title;

    public ?string $hero_subtitle;

    public ?string $hero_image;

    public int $free_shipping_threshold;

    public ?string $products_palette;

    public ?string $activities_palette;

    public ?string $bookings_palette;

    public ?string $cars_palette;

    public static function group(): string
    {
        return 'general';
    }

    public static function defaults(): array
    {
        return [
            'site_name' => 'ZeroMonad',
            'site_logo' => null,
            'site_description' => null,
            'active_theme' => Theme::Products->value,
            'currency' => 'EUR',
            'tax_rate' => 0.0,
            'low_stock_threshold' => 5,
            'hero_title' => null,
            'hero_subtitle' => null,
            'hero_image' => null,
            'free_shipping_threshold' => 50,
            'products_palette' => 'indigo',
            'activities_palette' => 'emerald',
            'bookings_palette' => 'amber',
            'cars_palette' => 'slate',
        ];
    }

    public function theme(): Theme
    {
        return Theme::from($this->active_theme);
    }
}
