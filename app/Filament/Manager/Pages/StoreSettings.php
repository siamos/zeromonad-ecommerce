<?php

namespace App\Filament\Manager\Pages;

use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationLabel(): string
    {
        return 'Store Settings';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    protected static ?int $navigationSort = 75;

    public function getView(): string
    {
        return 'filament.manager.pages.store-settings';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);

        $this->form->fill([
            'free_shipping_threshold' => $settings->free_shipping_threshold,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Shipping')->schema([
                    TextInput::make('free_shipping_threshold')
                        ->label('Free Shipping Threshold')
                        ->numeric()
                        ->prefix('€')
                        ->required()
                        ->helperText('Orders above this amount qualify for free shipping. Set to 0 to always offer free shipping.'),
                ]),

                Actions::make($this->getFormActions()),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);

        $settings->free_shipping_threshold = (int) $data['free_shipping_threshold'];
        $settings->save();

        Notification::make()->title('Store settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Store Settings')
                ->action('save'),
        ];
    }
}
