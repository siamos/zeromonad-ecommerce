<?php

namespace Database\Seeders;

use App\Settings\GeneralSettings;
use App\Settings\PaymentSettings;
use Illuminate\Database\Seeder;

class GeneralSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $general = app(GeneralSettings::class);
        $general->site_name = 'ZeroMonad Store';
        $general->site_logo = null;
        $general->site_description = null;
        $general->active_theme = 'Products';
        $general->currency = 'EUR';
        $general->tax_rate = 24.0;
        $general->low_stock_threshold = 5;
        $general->hero_title = null;
        $general->hero_subtitle = null;
        $general->hero_image = null;
        $general->save();

        $payments = app(PaymentSettings::class);
        $payments->cash_enabled = true;
        $payments->bank_transfer_enabled = true;
        $payments->viva_wallet_enabled = false;
        $payments->cardlink_enabled = false;
        $payments->stripe_enabled = false;
        $payments->bank_accounts = [
            ['bank' => 'Piraeus',  'account_name' => 'ZeroMonad IKE', 'iban' => '', 'swift' => 'PIRBGRAA'],
            ['bank' => 'Alpha',    'account_name' => 'ZeroMonad IKE', 'iban' => '', 'swift' => 'CRBAGRAAXXX'],
            ['bank' => 'Eurobank', 'account_name' => 'ZeroMonad IKE', 'iban' => '', 'swift' => 'ERBKGRAA'],
            ['bank' => 'Ethniki',  'account_name' => 'ZeroMonad IKE', 'iban' => '', 'swift' => 'ETHNGRAA'],
        ];
        $payments->save();
    }
}
