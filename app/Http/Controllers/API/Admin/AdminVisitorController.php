<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductView;
use Illuminate\Http\Request;

class AdminVisitorController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductView::with('product')
            ->when($request->search, function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('ip_address', 'like', "%{$request->search}%");
            })
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->orderByDesc('viewed_at')
            ->paginate(20);

        return response()->json($query);
    }

    public function summary()
    {
        $topProducts = ProductView::selectRaw('product_id, COUNT(*) as views, COUNT(DISTINCT email) as unique_visitors')
            ->with('product:id,name,thumbnail')
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $topVisitors = ProductView::selectRaw('email, name, COUNT(*) as views, MAX(viewed_at) as last_seen')
            ->whereNotNull('email')
            ->groupBy('email', 'name')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $recentVisitors = ProductView::with('product:id,name')
            ->whereNotNull('email')
            ->orderByDesc('viewed_at')
            ->limit(10)
            ->get(['email', 'name', 'product_id', 'browser', 'device', 'platform', 'ip_address', 'viewed_at']);

        $stats = [
            'total_views'          => ProductView::count(),
            'logged_in_views'      => ProductView::whereNotNull('user_id')->count(),
            'guest_views'          => ProductView::whereNull('user_id')->count(),
            'unique_emails'        => ProductView::whereNotNull('email')->distinct('email')->count(),
        ];

        return response()->json(compact('stats', 'topProducts', 'topVisitors', 'recentVisitors'));
    }
}
