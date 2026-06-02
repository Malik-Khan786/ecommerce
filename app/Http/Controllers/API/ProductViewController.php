<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProductViewController extends Controller
{
    public function log(Request $request, int $productId)
    {
        $request->validate([
            'browser'  => 'nullable|string|max:200',
            'device'   => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
        ]);

        $user = auth('sanctum')->user();

        ProductView::create([
            'product_id'  => $productId,
            'user_id'     => $user?->id,
            'guest_token' => $request->header('X-Cart-Token'),
            'email'       => $user?->email,
            'name'        => $user?->name,
            'ip_address'  => $request->ip(),
            'browser'     => $request->input('browser'),
            'device'      => $request->input('device'),
            'platform'    => $request->input('platform'),
            'viewed_at'   => Carbon::now(),
        ]);

        return response()->json(['logged' => true]);
    }
}
