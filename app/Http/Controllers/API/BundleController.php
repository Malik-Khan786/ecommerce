<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::active()
            ->with(['products' => fn($q) => $q->select('products.id', 'products.name', 'products.slug', 'products.thumbnail', 'products.price', 'products.sale_price')])
            ->latest()
            ->get();

        return response()->json($bundles);
    }

    public function show(string $slug)
    {
        $bundle = Bundle::where('slug', $slug)
            ->with(['products' => fn($q) => $q->with('images')])
            ->firstOrFail();

        return response()->json($bundle);
    }

    public function addToCart(Request $request, Bundle $bundle)
    {
        $bundle->load('products');

        $cartItems = [];
        foreach ($bundle->products as $product) {
            $quantity = $product->pivot->quantity ?? 1;

            $existing = Cart::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $newQty = min($existing->quantity + $quantity, $product->stock ?: 9999);
                $existing->update(['quantity' => $newQty]);
                $cartItems[] = $existing->load('product');
            } else {
                $item = Cart::create([
                    'user_id'    => $request->user()->id,
                    'product_id' => $product->id,
                    'quantity'   => min($quantity, $product->stock ?: 9999),
                ]);
                $cartItems[] = $item->load('product');
            }
        }

        return response()->json($cartItems);
    }

    // Admin CRUD
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active'        => ['boolean'],
            'image'            => ['nullable', 'string'],
            'products'         => ['required', 'array'],
            'products.*.id'    => ['required', 'exists:products,id'],
            'products.*.quantity' => ['integer', 'min:1'],
        ]);

        $bundle = Bundle::create([
            'name'             => $data['name'],
            'slug'             => Str::slug($data['name']),
            'description'      => $data['description'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? 0,
            'is_active'        => $data['is_active'] ?? true,
            'image'            => $data['image'] ?? null,
        ]);

        $syncData = [];
        foreach ($data['products'] as $p) {
            $syncData[$p['id']] = ['quantity' => $p['quantity'] ?? 1];
        }
        $bundle->products()->sync($syncData);

        return response()->json($bundle->load('products'), 201);
    }

    public function update(Request $request, Bundle $bundle)
    {
        $data = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active'        => ['boolean'],
            'image'            => ['nullable', 'string'],
            'products'         => ['sometimes', 'array'],
            'products.*.id'    => ['required_with:products', 'exists:products,id'],
            'products.*.quantity' => ['integer', 'min:1'],
        ]);

        $bundle->update(array_filter([
            'name'             => $data['name'] ?? null,
            'slug'             => isset($data['name']) ? Str::slug($data['name']) : null,
            'description'      => $data['description'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? null,
            'is_active'        => $data['is_active'] ?? null,
            'image'            => $data['image'] ?? null,
        ], fn($v) => $v !== null));

        if (isset($data['products'])) {
            $syncData = [];
            foreach ($data['products'] as $p) {
                $syncData[$p['id']] = ['quantity' => $p['quantity'] ?? 1];
            }
            $bundle->products()->sync($syncData);
        }

        return response()->json($bundle->load('products'));
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();
        return response()->json(['message' => 'Bundle deleted.']);
    }
}
