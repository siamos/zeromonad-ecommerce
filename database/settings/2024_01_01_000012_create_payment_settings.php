<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payments.cash_enabled', true);
        $this->migrator->add('payments.bank_transfer_enabled', true);
        $this->migrator->add('payments.viva_wallet_enabled', false);
        $this->migrator->add('payments.cardlink_enabled', false);
        $this->migrator->add('payments.stripe_enabled', false);

        $this->migrator->add('payments.viva_merchant_id', null);
        $this->migrator->add('payments.viva_api_key', null);
        $this->migrator->add('payments.viva_client_id', null);
        $this->migrator->add('payments.viva_client_secret', null);
        $this->migrator->add('payments.viva_use_sandbox', true);

        $this->migrator->add('payments.cardlink_merchant_id', null);
        $this->migrator->add('payments.cardlink_shared_secret', null);
        $this->migrator->add('payments.cardlink_use_sandbox', true);

        $this->migrator->add('payments.stripe_key', null);
        $this->migrator->add('payments.stripe_secret', null);

        $this->migrator->add('payments.bank_accounts', [
            ['bank' => 'Piraeus',  'account_name' => '', 'iban' => '', 'swift' => 'PIRBGRAA'],
            ['bank' => 'Alpha',    'account_name' => '', 'iban' => '', 'swift' => 'CRBAGRAAXXX'],
            ['bank' => 'Eurobank', 'account_name' => '', 'iban' => '', 'swift' => 'ERBKGRAA'],
            ['bank' => 'Ethniki',  'account_name' => '', 'iban' => '', 'swift' => 'ETHNGRAA'],
        ]);
    }
};
