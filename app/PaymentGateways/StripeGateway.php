<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\PaymentGateways\Contracts\PaymentGateway;
use App\PaymentGateways\Contracts\PaymentResult;
use App\Settings\PaymentSettings;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    public function __construct(private PaymentSettings $settings) {}

    public function charge(Order $order, array $payload): PaymentResult
    {
        try {
            $stripe = new StripeClient($this->settings->stripe_secret);

            $intent = $stripe->paymentIntents->create([
                'amount'               => (int) ($order->total * 100),
                'currency'             => strtolower(config('general.currency', 'eur')),
                'payment_method'       => $payload['payment_method_id'],
                'confirm'              => true,
                'metadata'             => ['order_number' => $order->order_number],
                'return_url'           => route('checkout.callback', 'stripe'),
            ]);

            if ($intent->status === 'succeeded') {
                return PaymentResult::success($intent->id);
            }

            if ($intent->status === 'requires_action') {
                return PaymentResult::redirect($intent->next_action->redirect_to_url->url, $intent->id);
            }

            return PaymentResult::failure("Payment status: {$intent->status}");
        } catch (ApiErrorException $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function verify(string $transactionId): PaymentResult
    {
        try {
            $stripe = new StripeClient($this->settings->stripe_secret);
            $intent = $stripe->paymentIntents->retrieve($transactionId);

            if ($intent->status === 'succeeded') {
                return PaymentResult::success($transactionId);
            }

            return PaymentResult::failure("Payment not complete: {$intent->status}");
        } catch (ApiErrorException $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function refund(Order $order, float $amount): bool
    {
        try {
            $stripe = new StripeClient($this->settings->stripe_secret);
            $stripe->refunds->create([
                'payment_intent' => $order->payment_intent_id,
                'amount'         => (int) ($amount * 100),
            ]);

            return true;
        } catch (ApiErrorException $e) {
            return false;
        }
    }

    public function getKey(): string
    {
        return 'stripe';
    }

    public function getLabel(): string
    {
        return 'Credit / Debit Card (Stripe)';
    }

    public function requiresRedirect(): bool
    {
        return false;
    }
}
