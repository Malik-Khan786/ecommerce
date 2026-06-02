<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return response()->json(['message' => 'Empty or invalid CSV file.'], 422);
        }

        // Normalize headers
        $headers = array_map(fn($h) => trim(strtolower(str_replace(' ', '_', $h))), $headers);

        $required = ['name', 'category_slug', 'price', 'stock'];
        $missing  = array_diff($required, $headers);
        if (!empty($missing)) {
            fclose($handle);
            return response()->json([
                'message' => 'Missing required columns: ' . implode(', ', $missing),
            ], 422);
        }

        $imported = 0;
        $failed   = [];
        $rowNum   = 1;
        $maxRows  = 500;

        while (($row = fgetcsv($handle)) !== false) {
            if ($rowNum > $maxRows) {
                $failed[] = ['row' => $rowNum + 1, 'reason' => "Max {$maxRows} rows per import exceeded."];
                break;
            }

            $data = array_combine($headers, array_pad($row, count($headers), null));
            $rowNum++;

            // Validate required fields
            if (empty(trim($data['name'] ?? ''))) {
                $failed[] = ['row' => $rowNum, 'reason' => 'Name is required.'];
                continue;
            }
            if (empty(trim($data['category_slug'] ?? ''))) {
                $failed[] = ['row' => $rowNum, 'reason' => 'category_slug is required.'];
                continue;
            }
            if (!is_numeric($data['price'] ?? null) || $data['price'] < 0) {
                $failed[] = ['row' => $rowNum, 'reason' => 'Price must be a non-negative number.'];
                continue;
            }
            if (!is_numeric($data['stock'] ?? null) || $data['stock'] < 0) {
                $failed[] = ['row' => $rowNum, 'reason' => 'Stock must be a non-negative integer.'];
                continue;
            }

            // Find or create category
            $category = Category::where('slug', trim($data['category_slug']))->first();
            if (!$category) {
                $catName  = Str::title(str_replace(['-', '_'], ' ', trim($data['category_slug'])));
                $category = Category::firstOrCreate(
                    ['slug' => trim($data['category_slug'])],
                    ['name' => $catName, 'is_active' => true]
                );
            }

            // Find or create brand
            $brand = null;
            if (!empty(trim($data['brand_slug'] ?? ''))) {
                $brand = Brand::where('slug', trim($data['brand_slug']))->first();
                if (!$brand) {
                    $brandName = Str::title(str_replace(['-', '_'], ' ', trim($data['brand_slug'])));
                    $brand = Brand::firstOrCreate(
                        ['slug' => trim($data['brand_slug'])],
                        ['name' => $brandName, 'is_active' => true]
                    );
                }
            }

            try {
                Product::create([
                    'category_id'       => $category->id,
                    'brand_id'          => $brand?->id,
                    'name'              => trim($data['name']),
                    'slug'              => Str::slug(trim($data['name'])) . '-' . uniqid(),
                    'description'       => trim($data['description'] ?? ''),
                    'price'             => (float) $data['price'],
                    'sale_price'        => isset($data['sale_price']) && is_numeric($data['sale_price']) && $data['sale_price'] > 0
                                            ? (float) $data['sale_price'] : null,
                    'stock'             => (int) $data['stock'],
                    'thumbnail'         => trim($data['thumbnail'] ?? '') ?: null,
                    'is_active'         => true,
                    'is_featured'       => false,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $failed[] = ['row' => $rowNum, 'reason' => $e->getMessage()];
            }
        }

        fclose($handle);

        return response()->json([
            'imported' => $imported,
            'failed'   => $failed,
            'total'    => $imported + count($failed),
        ]);
    }

    public function template()
    {
        $headers = ['name', 'category_slug', 'brand_slug', 'price', 'sale_price', 'stock', 'description', 'thumbnail'];
        $sample  = [
            'Samsung Galaxy A55', 'smartphones', 'samsung', '75000', '69999', '50',
            'Latest Samsung Galaxy A55 with 6.6\" display', 'https://example.com/image.jpg',
        ];

        $csv = implode(',', $headers) . "\n" . implode(',', $sample) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-import-template.csv"',
        ]);
    }
}
