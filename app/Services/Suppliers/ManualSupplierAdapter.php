<?php

namespace App\Services\Suppliers;

use App\Models\Order;
use App\Models\Product;

class ManualSupplierAdapter implements SupplierAdapter
{
    public function name(): string
    {
        return 'manual';
    }

    public function supports(Product $product): bool
    {
        return in_array($product->supplier_adapter, ['manual', null], true) || $product->supplier === 'manual';
    }

    public function placeOrder(Order $order): SupplierResult
    {
        return SupplierResult::unavailable('Manual fulfillment required. An administrator must complete this order.');
    }

    public function orderStatus(Order $order): SupplierResult
    {
        return SupplierResult::success($order->supplier_reference, ['status' => $order->status]);
    }

    public function catalog(): array
    {
        return [];
    }
}
