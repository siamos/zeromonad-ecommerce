<?php

namespace App\PaymentGateways\Contracts;

class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public static function success(string $transactionId, ?string $redirectUrl = null): self
    {
        return new self(
            success: true,
            transactionId: $transactionId,
            redirectUrl: $redirectUrl,
        );
    }

    public static function failure(string $message): self
    {
        return new self(
            success: false,
            errorMessage: $message,
        );
    }

    public static function redirect(string $url, string $transactionId = ''): self
    {
        return new self(
            success: true,
            transactionId: $transactionId,
            redirectUrl: $url,
        );
    }
}
