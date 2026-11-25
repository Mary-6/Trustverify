<?php

namespace App\Services\Suppliers;

use App\Models\Product;
use InvalidArgumentException;

class SupplierManager
{
    public function adapterFor(?Product $product = null): SupplierAdapter
    {
        $supplier = $product?->supplier;
        $name = $product?->supplier_adapter ?: (in_array($supplier, ['faussms', 'textverified', 'herosms', 'justanotherpanel'], true) ? $supplier : 'manual');

        return $this->adapter($name);
    }

    public function adapter(string $name): SupplierAdapter
    {
        return match ($name) {
            'manual' => app(ManualSupplierAdapter::class),
            'faussms' => app(FaussmsSupplierAdapter::class),
            'textverified' => app(TextVerifiedSupplierAdapter::class),
            'herosms' => app(HeroSmsSupplierAdapter::class),
            'justanotherpanel' => app(JustAnotherPanelSupplierAdapter::class),
            default => throw new InvalidArgumentException("Unsupported supplier adapter [{$name}]."),
        };
    }
}
