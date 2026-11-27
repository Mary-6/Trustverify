<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;

interface SupportsOrderActions
{
    public function canRefill(Product $product): bool;

    public function canCancel(Product $product): bool;

    public function requestRefill(Order $order): SupplierResult;

    public function cancelOrder(Order $order): SupplierResult;
}
