<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\PaymentGateways\Contracts\PaymentGateway;
use App\PaymentGateways\Contracts\PaymentResult;
use App\Settings\PaymentSettings;

class BankTransferGateway implements PaymentGateway
{
    public function __construct(private PaymentSettings $settings) {}

    public function charge(Order $order, array $payload): PaymentResult
    {
        return PaymentResult::success('BANK-' . $order->order_number);
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
        return 'bank_transfer';
    }

    public function getLabel(): string
    {
        return 'Bank Transfer';
    }

    public function requiresRedirect(): bool
    {
        return false;
    }

    public function getBankAccounts(): array
    {
        return array_filter(
            $this->settings->bank_accounts,
            fn ($account) => ! empty($account['iban'])
        );
    }
}
