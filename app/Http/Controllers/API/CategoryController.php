<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    public function show(string $slug)
    {
        $category = Category::with(['children', 'products' => fn($q) => $q->where('is_active', true)->take(4)])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($category);
    }
}
