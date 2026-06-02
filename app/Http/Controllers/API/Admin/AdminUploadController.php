<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('image')->store('products', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate(['path' => 'required|string']);

        $relativePath = str_replace('/storage/', '', $request->path);
        Storage::disk('public')->delete($relativePath);

        return response()->json(['message' => 'Deleted.']);
    }
}
