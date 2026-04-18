<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\PaymentGateways\Contracts\PaymentGateway;
use App\PaymentGateways\Contracts\PaymentResult;
use App\Settings\PaymentSettings;
use Illuminate\Support\Facades\Http;

class CardlinkGateway implements PaymentGateway
{
    private const ENDPOINT_PRODUCTION = 'https://paycenter.piraeusbank.gr/redirection/pay';
    private const ENDPOINT_SANDBOX    = 'https://paycenter.piraeusbank.gr/redirection/pay';

    public function __construct(private PaymentSettings $settings) {}

    public function getKey(): string        { return 'cardlink'; }
    public function getLabel(): string      { return 'Cardlink (Alpha / Eurobank)'; }
    public function requiresRedirect(): bool { return true; }

    public function charge(Order $order, array $payload): PaymentResult
    {
        try {
            $params = $this->buildParams($order);

            // Store params in session so the redirect page can auto-submit them
            session(['cardlink_params' => $params, 'cardlink_endpoint' => $this->endpoint()]);

            $redirectUrl = route('checkout.callback', 'cardlink') . '?action=form&order=' . $order->id;

            return PaymentResult::redirect($redirectUrl, $order->order_number);
        } catch (\Throwable $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function verify(string $transactionId): PaymentResult
    {
        try {
            $post = request()->all();

            if (empty($post['orderid']) || empty($post['status'])) {
                return PaymentResult::failure('Invalid callback data.');
            }

            $digest = $this->calculateDigest($post);

            if (!hash_equals($digest, $post['digest'] ?? '')) {
                return PaymentResult::failure('Invalid digest — possible tampering.');
            }

            if (strtoupper($post['status']) === 'CAPTURED') {
                return PaymentResult::success($post['txId'] ?? $post['orderid']);
            }

            return PaymentResult::failure("Payment status: {$post['status']}");
        } catch (\Throwable $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function refund(Order $order, float $amount): bool
    {
        return false;
    }

    private function buildParams(Order $order): array
    {
        $params = [
            'mid'         => $this->settings->cardlink_merchant_id,
            'lang'        => 'el',
            'orderid'     => $order->order_number,
            'orderDesc'   => "Order {$order->order_number}",
            'orderAmount' => number_format($order->total, 2, '.', ''),
            'currency'    => '978',
            'payerEmail'  => $order->billing_address['email'] ?? '',
            'payerPhone'  => $order->billing_address['phone'] ?? '',
            'billAddr'    => $order->billing_address['line1'] ?? '',
            'billCity'    => $order->billing_address['city'] ?? '',
            'billZip'     => $order->billing_address['zip'] ?? '',
            'billCountry' => 'GR',
            'trType'      => '1',
            'confirmUrl'  => route('checkout.callback', 'cardlink') . '?order=' . $order->id,
            'cancelUrl'   => route('checkout.index'),
        ];

        $params['digest'] = $this->calculateDigest($params);

        return $params;
    }

    private function calculateDigest(array $params): string
    {
        $fields = [
            'mid', 'lang', 'orderid', 'orderDesc', 'orderAmount',
            'currency', 'payerEmail', 'confirmUrl', 'cancelUrl',
        ];

        $digestStr = implode('', array_map(fn ($k) => $params[$k] ?? '', $fields));
        $digestStr .= $this->settings->cardlink_shared_secret;

        return base64_encode(hash('sha256', $digestStr, true));
    }

    private function endpoint(): string
    {
        return $this->settings->cardlink_use_sandbox
            ? self::ENDPOINT_SANDBOX
            : self::ENDPOINT_PRODUCTION;
    }
}
