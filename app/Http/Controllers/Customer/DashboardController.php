<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VirtualNumber;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function overview(Request $request)
    {
        $user = $request->user();
        $wallet = app(\App\Services\WalletService::class)->walletFor($user);

        return view('customer.dashboard', [
            'wallet' => $wallet,
            'recentOrders' => $user->orders()->with('product')->latest()->limit(6)->get(),
            'ordersCount' => $user->orders()->count(),
            'openTickets' => $user->supportTickets()->whereNotIn('status', ['closed'])->count(),
            'activeNumbers' => VirtualNumber::where('user_id', $user->id)->where('status', 'active')->count(),
        ]);
    }

    public function orders(Request $request)
    {
        $orders = $request->user()->orders()->with('product')->latest()->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    public function order(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return view('customer.orders.show', ['order' => $order->load('product', 'histories', 'user')]);
    }

    public function numbers(Request $request)
    {
        $numbers = VirtualNumber::where('user_id', $request->user()->id)
            ->with(['country', 'messages'])
            ->latest()
            ->paginate(15);

        return view('customer.numbers.index', compact('numbers'));
    }
}
