<?php

namespace App\PaymentGateways\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    public function charge(Order $order, array $payload): PaymentResult;

    public function verify(string $transactionId): PaymentResult;

    public function refund(Order $order, float $amount): bool;

    public function getKey(): string;

    public function getLabel(): string;

    public function requiresRedirect(): bool;
}
