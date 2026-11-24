<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VirtualNumber;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function overview(Request $request)
    {
        $user = $request->user();
        $wallet = app(\App\Services\WalletService::class)->walletFor($user);
        $referrals = app(ReferralService::class);

        return view('customer.dashboard', [
            'wallet' => $wallet,
            'recentOrders' => $user->orders()->with('product')->latest()->limit(6)->get(),
            'ordersCount' => $user->orders()->count(),
            'openTickets' => $user->supportTickets()->whereNotIn('status', ['closed'])->count(),
            'activeNumbers' => VirtualNumber::where('user_id', $user->id)->where('status', 'active')->count(),
            'referralCode' => $referrals->ensureCode($user),
            'referralUrl' => $referrals->referralUrl($user),
            'referredCount' => $user->referrals()->count(),
            'rewardedReferrals' => $user->referrals()->whereNotNull('referral_rewarded_at')->count(),
        ]);
    }

    public function orders(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $statuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_SUBMITTED,
            Order::STATUS_COMPLETED,
            Order::STATUS_FAILED,
            Order::STATUS_CANCELLED,
            Order::STATUS_REFUNDED,
        ];

        $orders = $request->user()->orders()
            ->with('product')
            ->when($query !== '', function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $builder->where(function ($nested) use ($like) {
                    $nested->where('order_number', 'like', $like)
                        ->orWhere('product_name', 'like', $like)
                        ->orWhere('customer_notes', 'like', $like)
                        ->orWhere('payload->link', 'like', $like);
                });
            })
            ->when(in_array($status, $statuses, true), fn ($builder) => $builder->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customer.orders.index', [
            'orders' => $orders,
            'query' => $query,
            'status' => $status,
            'statuses' => $statuses,
        ]);
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
