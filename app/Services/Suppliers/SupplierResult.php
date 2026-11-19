<?php

namespace App\Services\Suppliers;

class SupplierResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $message = null,
        public readonly ?string $reference = null,
        public readonly array $data = [],
        public readonly bool $safeToRefund = false,
    ) {}

    public static function success(?string $reference = null, array $data = [], ?string $message = null): self
    {
        return new self('success', $message, $reference, $data, false);
    }

    public static function failed(string $message, bool $safeToRefund = false, array $data = []): self
    {
        return new self('failed', $message, null, $data, $safeToRefund);
    }

    public static function unavailable(string $message): self
    {
        return new self('unavailable', $message, null, [], false);
    }
}
