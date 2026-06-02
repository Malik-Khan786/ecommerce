<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AbandonedCartRecovery;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminAbandonedCartController extends Controller
{
    public function index()
    {
        $cutoff = now()->subHours(2);

        // Get user IDs with abandoned carts (no order in last 2h)
        $userIds = Cart::whereNotNull('user_id')
            ->where('created_at', '<=', $cutoff)
            ->distinct()
            ->pluck('user_id');

        // Filter out users who placed orders recently
        $recentOrderUserIds = Order::whereIn('user_id', $userIds)
            ->where('created_at', '>=', $cutoff)
            ->distinct()
            ->pluck('user_id');

        $abandonedUserIds = $userIds->diff($recentOrderUserIds);

        $count = $abandonedUserIds->count();

        $users = User::whereIn('id', $abandonedUserIds)
            ->with(['carts' => function ($q) use ($cutoff) {
                $q->with('product')->where('created_at', '<=', $cutoff);
            }])
            ->paginate(20);

        return response()->json([
            'count' => $count,
            'users' => $users,
        ]);
    }

    public function sendRecovery(int $userId)
    {
        $cutoff = now()->subHours(2);

        $user = User::findOrFail($userId);

        // Check for recent order
        $hasRecentOrder = Order::where('user_id', $userId)
            ->where('created_at', '>=', $cutoff)
            ->exists();

        if ($hasRecentOrder) {
            return response()->json(['message' => 'User has placed a recent order.'], 422);
        }

        $cartItems = Cart::with('product')
            ->where('user_id', $userId)
            ->where('created_at', '<=', $cutoff)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'No abandoned cart items found.'], 404);
        }

        Mail::to($user->email)->send(new AbandonedCartRecovery($user, $cartItems));

        return response()->json(['message' => 'Recovery email sent.']);
    }
}
