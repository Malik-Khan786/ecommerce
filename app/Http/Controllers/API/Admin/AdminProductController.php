<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'sku'               => 'nullable|string|unique:products',
            'thumbnail'         => 'nullable|string',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'scheduled_at'      => 'nullable|date',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        $product = Product::create($data);

        return response()->json($product->load(['category', 'brand']), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'brand', 'images']));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id'       => 'sometimes|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'name'              => 'sometimes|string|max:255',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'sometimes|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'sometimes|integer|min:0',
            'thumbnail'         => 'nullable|string',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'scheduled_at'      => 'nullable|date',
        ]);

        $product->update($data);
        return response()->json($product->load(['category', 'brand']));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted.']);
    }
}
