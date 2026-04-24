<?php

namespace App\Filament\Admin\Pages;

use App\Actions\Settings\SwitchTheme;
use App\Enums\Theme;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationLabel(): string
    {
        return 'Site Settings';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    protected static ?int $navigationSort = 90;

    public function getView(): string
    {
        return 'filament.admin.pages.site-settings';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);

        $this->form->fill([
            'site_name' => $settings->site_name,
            'site_description' => $settings->site_description,
            'active_theme' => $settings->active_theme,
            'currency' => $settings->currency,
            'tax_rate' => $settings->tax_rate,
            'low_stock_threshold' => $settings->low_stock_threshold,
            'free_shipping_threshold' => $settings->free_shipping_threshold,
            'products_palette' => $settings->products_palette,
            'activities_palette' => $settings->activities_palette,
            'bookings_palette' => $settings->bookings_palette,
            'cars_palette' => $settings->cars_palette,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('site_name')->required()->label('Site Name'),
                Textarea::make('site_description')
                    ->label('Site Description')
                    ->helperText('Default meta description used for SEO across all pages.')
                    ->rows(3)
                    ->maxLength(300)
                    ->columnSpanFull(),
                Select::make('active_theme')
                    ->options(Theme::class)
                    ->required()
                    ->label('Active Theme')
                    ->helperText('Changes take effect on the next page load — no rebuild required.'),
                TextInput::make('currency')->required()->maxLength(3)->label('Currency (ISO code)'),
                TextInput::make('tax_rate')->numeric()->suffix('%')->label('Tax Rate'),
                TextInput::make('low_stock_threshold')->numeric()->label('Low Stock Alert Threshold'),
                TextInput::make('free_shipping_threshold')->numeric()->prefix('€')->label('Free Shipping Threshold'),
                Select::make('products_palette')
                    ->label('Products Theme Palette')
                    ->options(['indigo' => 'Indigo', 'violet' => 'Violet', 'blue' => 'Blue', 'emerald' => 'Emerald'])
                    ->required(),
                Select::make('activities_palette')
                    ->label('Activities Theme Palette')
                    ->options(['emerald' => 'Emerald', 'teal' => 'Teal', 'cyan' => 'Cyan', 'green' => 'Green'])
                    ->required(),
                Select::make('bookings_palette')
                    ->label('Bookings Theme Palette')
                    ->options(['amber' => 'Amber', 'orange' => 'Orange', 'rose' => 'Rose', 'pink' => 'Pink'])
                    ->required(),
                Select::make('cars_palette')
                    ->label('Cars Theme Palette')
                    ->options(['slate' => 'Slate', 'zinc' => 'Zinc', 'stone' => 'Stone', 'neutral' => 'Neutral'])
                    ->required(),
                Actions::make($this->getFormActions()),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);

        $oldTheme = $settings->active_theme;

        $settings->site_name = $data['site_name'];
        $settings->site_description = $data['site_description'] ?: null;
        $settings->currency = $data['currency'];
        $settings->tax_rate = (float) $data['tax_rate'];
        $settings->low_stock_threshold = (int) $data['low_stock_threshold'];
        $settings->free_shipping_threshold = (int) $data['free_shipping_threshold'];
        $settings->products_palette = $data['products_palette'];
        $settings->activities_palette = $data['activities_palette'];
        $settings->bookings_palette = $data['bookings_palette'];
        $settings->cars_palette = $data['cars_palette'];
        $settings->save();

        if ($data['active_theme'] !== $oldTheme) {
            SwitchTheme::run($data['active_theme']);
        }

        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('save'),
        ];
    }
}
