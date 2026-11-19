<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;

class PricingService
{
    public function __construct(private SettingsService $settings) {}

    public function unitPrice(Product $product): string
    {
        if ($product->price !== null) {
            return Money::normalize($product->price);
        }

        $cost = $product->supplier_cost ?? 0;
        $markup = (float) $this->settings->get('pricing.global_markup_percent', 30);
        $fixed = (float) $this->settings->get('pricing.global_markup_fixed', 0);

        return Money::add(Money::mul($cost, 1 + ($markup / 100)), $fixed);
    }

    public function quote(Product $product, int $quantity): array
    {
        $unit = $this->unitPrice($product);
        $total = $product->isPer1000()
            ? $this->per1000Total($unit, $quantity)
            : Money::mul($unit, $quantity);

        return [
            'unit_price' => $unit,
            'total_price' => $total,
            'currency' => $product->currency ?: 'XAF',
            'supplier_cost' => $product->supplier_cost ? Money::normalize($product->supplier_cost) : null,
            'pricing_unit' => $product->pricingUnit(),
        ];
    }

    private function per1000Total(string $unit, int $quantity): string
    {
        return Money::normalize(bcdiv(bcmul($unit, (string) $quantity, 4), '1000', 4));
    }
}
