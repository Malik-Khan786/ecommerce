<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function compare(Request $request)
    {
        $request->validate([
            'product_ids'   => ['required', 'array', 'max:3'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $products = Product::with(['category', 'brand', 'images', 'variants', 'reviews'])
            ->whereIn('id', $request->product_ids)
            ->get();

        $products = $products->map(function (Product $product) {
            $reviews    = $product->reviews;
            $avgRating  = $reviews->count()
                ? round($reviews->avg('rating'), 1)
                : null;

            $product->specs = [
                'price'          => $product->price,
                'sale_price'     => $product->sale_price,
                'effective_price'=> $product->effective_price,
                'stock'          => $product->stock,
                'avg_rating'     => $avgRating,
                'review_count'   => $reviews->count(),
                'category'       => $product->category?->name,
                'brand'          => $product->brand?->name,
            ];

            return $product;
        });

        return response()->json($products);
    }
}
