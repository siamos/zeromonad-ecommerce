<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\PaymentGateways\Contracts\PaymentGateway;
use App\PaymentGateways\Contracts\PaymentResult;
use App\Settings\PaymentSettings;
use Illuminate\Support\Facades\Http;

class VivaWalletGateway implements PaymentGateway
{
    public function __construct(private PaymentSettings $settings) {}

    public function charge(Order $order, array $payload): PaymentResult
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->post($this->apiUrl('checkout/v2/orders'), [
                    'amount'          => (int) ($order->total * 100),
                    'customerTrns'    => "Order #{$order->order_number}",
                    'email'           => $order->user?->email ?? $payload['email'] ?? '',
                    'fullName'        => $order->user?->name ?? $payload['name'] ?? '',
                    'requestLang'     => 'el-GR',
                    'sourceCode'      => $this->settings->viva_source_code ?? 'Default',
                    'merchantTrns'    => $order->order_number,
                    'allowRecurring'  => false,
                    'maxInstallments' => 0,
                ]);

            $orderCode = $response->json('orderCode');

            $checkoutUrl = $this->checkoutUrl($orderCode);

            return PaymentResult::redirect($checkoutUrl, (string) $orderCode);
        } catch (\Exception $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function verify(string $transactionId): PaymentResult
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->get($this->apiUrl("checkout/v2/transactions/{$transactionId}"));

            $status = $response->json('StatusId');

            if ($status === 'F') {
                return PaymentResult::success($transactionId);
            }

            return PaymentResult::failure("Transaction status: {$status}");
        } catch (\Exception $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function refund(Order $order, float $amount): bool
    {
        try {
            $token = $this->getAccessToken();

            Http::withToken($token)
                ->delete($this->apiUrl("checkout/v2/transactions/{$order->payment_gateway_transaction_id}"), [
                    'amount' => (int) ($amount * 100),
                ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getKey(): string
    {
        return 'viva_wallet';
    }

    public function getLabel(): string
    {
        return 'Viva Wallet / IRIS';
    }

    public function requiresRedirect(): bool
    {
        return true;
    }

    private function getAccessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth($this->settings->viva_client_id, $this->settings->viva_client_secret)
            ->post($this->accountsUrl('connect/token'), [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json('access_token');
    }

    private function isSandbox(): bool
    {
        return (bool) $this->settings->viva_use_sandbox;
    }

    private function apiUrl(string $path): string
    {
        $base = $this->isSandbox()
            ? 'https://demo-api.vivapayments.com'
            : 'https://api.vivapayments.com';

        return "{$base}/{$path}";
    }

    private function accountsUrl(string $path): string
    {
        $base = $this->isSandbox()
            ? 'https://demo-accounts.vivapayments.com'
            : 'https://accounts.vivapayments.com';

        return "{$base}/{$path}";
    }

    private function checkoutUrl(string $orderCode): string
    {
        $base = $this->isSandbox()
            ? 'https://demo.vivapayments.com/web/checkout'
            : 'https://www.vivapayments.com/web/checkout';

        return "{$base}?ref={$orderCode}";
    }
}
