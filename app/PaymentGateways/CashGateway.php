<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\PaymentGateways\Contracts\PaymentGateway;
use App\PaymentGateways\Contracts\PaymentResult;

class CashGateway implements PaymentGateway
{
    public function charge(Order $order, array $payload): PaymentResult
    {
        return PaymentResult::success('CASH-' . $order->order_number);
    }

    public function verify(string $transactionId): PaymentResult
    {
        return PaymentResult::success($transactionId);
    }

    public function refund(Order $order, float $amount): bool
    {
        return true;
    }

    public function getKey(): string
    {
        return 'cash';
    }

    public function getLabel(): string
    {
        return 'Cash on Delivery';
    }

    public function requiresRedirect(): bool
    {
        return false;
    }
}
