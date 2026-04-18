<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    // Enabled flags
    public bool $cash_enabled;
    public bool $bank_transfer_enabled;
    public bool $viva_wallet_enabled;
    public bool $cardlink_enabled;
    public bool $stripe_enabled;

    // Viva Wallet
    public ?string $viva_merchant_id;
    public ?string $viva_api_key;
    public ?string $viva_client_id;
    public ?string $viva_client_secret;
    public bool $viva_use_sandbox;

    // Cardlink
    public ?string $cardlink_merchant_id;
    public ?string $cardlink_shared_secret;
    public bool $cardlink_use_sandbox;

    // Stripe
    public ?string $stripe_key;
    public ?string $stripe_secret;

    // Bank accounts: [{bank, account_name, iban, swift}]
    public array $bank_accounts;

    public static function group(): string
    {
        return 'payments';
    }

    public static function defaults(): array
    {
        return [
            'cash_enabled'          => true,
            'bank_transfer_enabled' => true,
            'viva_wallet_enabled'   => false,
            'cardlink_enabled'      => false,
            'stripe_enabled'        => false,

            'viva_merchant_id'    => null,
            'viva_api_key'        => null,
            'viva_client_id'      => null,
            'viva_client_secret'  => null,
            'viva_use_sandbox'    => true,

            'cardlink_merchant_id'    => null,
            'cardlink_shared_secret'  => null,
            'cardlink_use_sandbox'    => true,

            'stripe_key'    => null,
            'stripe_secret' => null,

            'bank_accounts' => [
                ['bank' => 'Piraeus',  'account_name' => '', 'iban' => '', 'swift' => 'PIRBGRAA'],
                ['bank' => 'Alpha',    'account_name' => '', 'iban' => '', 'swift' => 'CRBAGRAAXXX'],
                ['bank' => 'Eurobank', 'account_name' => '', 'iban' => '', 'swift' => 'ERBKGRAA'],
                ['bank' => 'Ethniki',  'account_name' => '', 'iban' => '', 'swift' => 'ETHNGRAA'],
            ],
        ];
    }
}
