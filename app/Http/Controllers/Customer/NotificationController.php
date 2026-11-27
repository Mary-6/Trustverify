<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function show(Request $request, DatabaseNotification $notification)
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard.overview'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Notifications marked as read.');
    }

    private function authorizeNotification(Request $request, DatabaseNotification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === User::class
                && (int) $notification->notifiable_id === (int) $request->user()->id,
            403
        );
    }
}
