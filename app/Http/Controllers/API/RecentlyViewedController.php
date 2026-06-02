<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RecentlyViewed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RecentlyViewedController extends Controller
{
    public function index(Request $request)
    {
        $query = RecentlyViewed::with('product.brand')
            ->orderByDesc('viewed_at')
            ->limit(8);

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $token = $request->header('X-Cart-Token');
            if (!$token) return response()->json([]);
            $query->where('session_token', $token);
        }

        return response()->json($query->get());
    }

    public function store(Request $request, int $productId)
    {
        $userId = $request->user()?->id;
        $token  = $request->header('X-Cart-Token');

        $conditions = $userId
            ? ['user_id' => $userId, 'product_id' => $productId]
            : ['session_token' => $token, 'product_id' => $productId];

        RecentlyViewed::updateOrCreate($conditions, ['viewed_at' => Carbon::now()]);

        // Keep only last 20 per user/session
        $ids = RecentlyViewed::when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $token, fn($q) => $q->where('session_token', $token))
            ->orderByDesc('viewed_at')
            ->skip(20)->take(999)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            RecentlyViewed::whereIn('id', $ids)->delete();
        }

        return response()->json(['logged' => true]);
    }
}
