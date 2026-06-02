<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function subscribe(Request $request, int $productId)
    {
        $request->validate(['email' => 'required|email']);

        $product = Product::findOrFail($productId);

        if ($product->stock > 0) {
            return response()->json(['message' => 'This product is already in stock!'], 422);
        }

        StockAlert::updateOrCreate(
            ['product_id' => $productId, 'email' => $request->email],
            ['notified_at' => null]
        );

        return response()->json(['message' => "We'll notify you at {$request->email} when it's back in stock!"], 201);
    }

    public function unsubscribe(Request $request, int $productId)
    {
        $request->validate(['email' => 'required|email']);

        StockAlert::where('product_id', $productId)
            ->where('email', $request->email)
            ->delete();

        return response()->json(['message' => 'Unsubscribed successfully.']);
    }
}
