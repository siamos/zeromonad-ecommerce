<?php

namespace App\Filament\Manager\Pages;

use App\Settings\PaymentSettings as PaymentSettingsModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-credit-card'; }
    public static function getNavigationLabel(): string { return 'Payment Settings'; }
    public static function getNavigationGroup(): ?string { return 'Settings'; }
    protected static ?int $navigationSort = 80;
    public function getView(): string { return 'filament.manager.pages.payment-settings'; }

    public ?array $data = [];

    public function mount(): void
    {
        $s = app(PaymentSettingsModel::class);

        $this->form->fill([
            'cash_enabled'          => $s->cash_enabled,
            'bank_transfer_enabled' => $s->bank_transfer_enabled,
            'viva_wallet_enabled'   => $s->viva_wallet_enabled,
            'cardlink_enabled'      => $s->cardlink_enabled,
            'stripe_enabled'        => $s->stripe_enabled,

            'viva_merchant_id'    => $s->viva_merchant_id,
            'viva_api_key'        => $s->viva_api_key,
            'viva_client_id'      => $s->viva_client_id,
            'viva_client_secret'  => $s->viva_client_secret,
            'viva_use_sandbox'    => $s->viva_use_sandbox,

            'cardlink_merchant_id'   => $s->cardlink_merchant_id,
            'cardlink_shared_secret' => $s->cardlink_shared_secret,
            'cardlink_use_sandbox'   => $s->cardlink_use_sandbox,

            'stripe_key'    => $s->stripe_key,
            'stripe_secret' => $s->stripe_secret,

            'bank_accounts' => $s->bank_accounts,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Enable / Disable Payment Methods')->schema([
                    Toggle::make('cash_enabled')->label('Cash on Delivery'),
                    Toggle::make('bank_transfer_enabled')->label('Bank Transfer'),
                    Toggle::make('viva_wallet_enabled')->label('Viva Wallet / IRIS'),
                    Toggle::make('cardlink_enabled')->label('Cardlink (Alpha / Eurobank)'),
                    Toggle::make('stripe_enabled')->label('Stripe'),
                ])->columns(2),

                Section::make('Bank Transfer — Account Details')
                    ->description('Customers will see all configured accounts at checkout.')
                    ->collapsed()
                    ->schema([
                        Repeater::make('bank_accounts')
                            ->label('')
                            ->schema([
                                Select::make('bank')
                                    ->options(['Piraeus' => 'Piraeus', 'Alpha' => 'Alpha', 'Eurobank' => 'Eurobank', 'Ethniki' => 'Ethniki'])
                                    ->required(),
                                TextInput::make('account_name')->required(),
                                TextInput::make('iban')->required()->placeholder('GR00 0000 0000 0000 0000 0000 000'),
                                TextInput::make('swift')->placeholder('PIRBGRAA'),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('Add Bank Account')
                            ->columnSpanFull(),
                    ]),

                Section::make('Viva Wallet / IRIS Credentials')
                    ->collapsed()
                    ->schema([
                        TextInput::make('viva_merchant_id')->label('Merchant ID'),
                        TextInput::make('viva_api_key')->label('API Key')->password()->revealable(),
                        TextInput::make('viva_client_id')->label('Client ID'),
                        TextInput::make('viva_client_secret')->label('Client Secret')->password()->revealable(),
                        Toggle::make('viva_use_sandbox')->label('Use Sandbox')->default(true),
                    ])->columns(2),

                Section::make('Cardlink Credentials')
                    ->collapsed()
                    ->schema([
                        TextInput::make('cardlink_merchant_id')->label('Merchant ID'),
                        TextInput::make('cardlink_shared_secret')->label('Shared Secret')->password()->revealable(),
                        Toggle::make('cardlink_use_sandbox')->label('Use Sandbox')->default(true),
                    ])->columns(2),

                Section::make('Stripe Credentials')
                    ->collapsed()
                    ->schema([
                        TextInput::make('stripe_key')->label('Publishable Key'),
                        TextInput::make('stripe_secret')->label('Secret Key')->password()->revealable(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $s    = app(PaymentSettingsModel::class);

        foreach ($data as $key => $value) {
            $s->{$key} = $value;
        }

        $s->save();

        Notification::make()->title('Payment settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Payment Settings')
                ->action('save'),
        ];
    }
}
