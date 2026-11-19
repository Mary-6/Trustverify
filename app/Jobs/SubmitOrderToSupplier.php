<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\SupplierFulfillmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitOrderToSupplier implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(SupplierFulfillmentService $fulfillment): void
    {
        $order = Order::with('product')->find($this->orderId);
        if ($order) {
            $fulfillment->submit($order);
        }
    }
}
