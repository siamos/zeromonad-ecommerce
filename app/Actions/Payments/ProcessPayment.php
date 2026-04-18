<?php

namespace App\Actions\Payments;

use App\Models\Order;
use App\PaymentGateways\Contracts\PaymentResult;
use App\PaymentGateways\PaymentGatewayManager;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessPayment
{
    use AsAction;

    public function handle(Order $order, array $payload = []): PaymentResult
    {
        $manager = app(PaymentGatewayManager::class);
        $gateway = $manager->resolve($order->payment_method);

        $result = $gateway->charge($order, $payload);

        if ($result->success) {
            $order->update([
                'payment_gateway_transaction_id' => $result->transactionId,
                'payment_intent_id'              => $result->transactionId,
            ]);

            if (! $gateway->requiresRedirect()) {
                $order->update([
                    'payment_status'     => 'paid',
                    'payment_verified_at' => now(),
                    'status'             => 'processing',
                ]);
            }
        }

        return $result;
    }
}
