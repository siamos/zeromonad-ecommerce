<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'ZeroMonad');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.active_theme', 'Products');
        $this->migrator->add('general.currency', 'EUR');
        $this->migrator->add('general.tax_rate', 0.0);
        $this->migrator->add('general.low_stock_threshold', 5);
    }
};
