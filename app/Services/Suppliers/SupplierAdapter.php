<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;

interface SupplierAdapter
{
    public function name(): string;

    public function supports(Product $product): bool;

    public function placeOrder(Order $order): SupplierResult;

    public function orderStatus(Order $order): SupplierResult;

    public function catalog(): array;
}
