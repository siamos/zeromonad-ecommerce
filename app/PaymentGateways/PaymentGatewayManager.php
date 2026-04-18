<?php

namespace App\PaymentGateways;

use App\PaymentGateways\Contracts\PaymentGateway;
use App\Settings\PaymentSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;

class PaymentGatewayManager
{
    private array $gateways = [];

    public function __construct(private Application $app)
    {
        $this->register();
    }

    private function register(): void
    {
        $settings = $this->app->make(PaymentSettings::class);

        $this->gateways = [
            'cash'          => fn () => new CashGateway(),
            'bank_transfer' => fn () => new BankTransferGateway($settings),
            'viva_wallet'   => fn () => new VivaWalletGateway($settings),
            'cardlink'      => fn () => new CardlinkGateway($settings),
            'stripe'        => fn () => new StripeGateway($settings),
        ];
    }

    public function resolve(string $key): PaymentGateway
    {
        if (! isset($this->gateways[$key])) {
            throw new \InvalidArgumentException("Payment gateway [{$key}] is not registered.");
        }

        return ($this->gateways[$key])();
    }

    public function enabled(): Collection
    {
        $settings = $this->app->make(PaymentSettings::class);

        return collect($this->gateways)
            ->filter(fn ($_, $key) => match ($key) {
                'cash'          => $settings->cash_enabled,
                'bank_transfer' => $settings->bank_transfer_enabled,
                'viva_wallet'   => $settings->viva_wallet_enabled,
                'cardlink'      => $settings->cardlink_enabled,
                'stripe'        => $settings->stripe_enabled,
                default         => false,
            })
            ->map(fn ($factory) => $factory());
    }

    public function available(): array
    {
        return $this->enabled()
            ->map(fn (PaymentGateway $gateway) => [
                'key'   => $gateway->getKey(),
                'label' => $gateway->getLabel(),
            ])
            ->values()
            ->toArray();
    }
}
