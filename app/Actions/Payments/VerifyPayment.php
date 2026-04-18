<?php

namespace App\Actions\Payments;

use App\Models\Order;
use App\PaymentGateways\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class VerifyPayment
{
    use AsAction;

    public function handle(Order $order, string $gatewayKey, string $transactionId): bool
    {
        $gateway = app(PaymentGatewayManager::class)->resolve($gatewayKey);
        $result  = $gateway->verify($transactionId);

        if ($result->success) {
            $order->update([
                'payment_status'                 => 'paid',
                'payment_verified_at'            => now(),
                'payment_gateway_transaction_id' => $result->transactionId,
                'status'                         => 'processing',
            ]);
        }

        return $result->success;
    }

    public function asController(Request $request, string $gateway): RedirectResponse
    {
        $transactionId = $request->query('t') ?? $request->query('s') ?? '';

        $order = Order::where('payment_gateway_transaction_id', $transactionId)
            ->orWhere('payment_intent_id', $transactionId)
            ->latest()
            ->first();

        if (! $order || ! $this->handle($order, $gateway, $transactionId)) {
            return redirect()->route('checkout.index')->with('error', 'Payment could not be verified.');
        }

        return redirect()->route('checkout.success', $order)->with('success', 'Payment confirmed!');
    }
}
