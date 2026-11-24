<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SupplierLog;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currency = strtoupper((string) app(SettingsService::class)->get('general.primary_currency', 'XAF'));

        return view('admin.dashboard', [
            'stats' => [
                'revenue' => Order::whereNotIn('status', [Order::STATUS_CANCELLED])->where('currency', $currency)->sum('total_price'),
                'currency' => $currency,
                'orders' => Order::count(),
                'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
                'active_products' => Product::active()->count(),
                'archived_products' => Product::where('status', 'archived')->count(),
                'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
                'pending_payments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'open_tickets' => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
                'supplier_failures' => SupplierLog::where('status', 'failed')->count(),
            ],
            'recentOrders' => Order::with(['user', 'product'])->latest()->limit(10)->get(),
        ]);
    }
}
