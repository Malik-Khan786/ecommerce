<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@bestnow.pk',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '03001234567',
        ]);

        User::create([
            'name'     => 'Test Customer',
            'email'    => 'customer@bestnow.pk',
            'password' => Hash::make('password'),
            'role'     => 'customer',
            'phone'    => '03009876543',
        ]);

        $brands = [
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Nike', 'slug' => 'nike'],
            ['name' => 'Adidas', 'slug' => 'adidas'],
            ['name' => 'Sony', 'slug' => 'sony'],
            ['name' => 'Gul Ahmed', 'slug' => 'gul-ahmed'],
        ];
        foreach ($brands as $brand) {
            Brand::create(array_merge($brand, ['is_active' => true]));
        }

        $categoryData = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'children' => [
                ['name' => 'Mobile Phones', 'slug' => 'mobile-phones'],
                ['name' => 'Laptops', 'slug' => 'laptops'],
                ['name' => 'Tablets', 'slug' => 'tablets'],
                ['name' => 'Audio', 'slug' => 'audio'],
            ]],
            ['name' => 'Fashion', 'slug' => 'fashion', 'children' => [
                ['name' => "Men's Clothing", 'slug' => 'mens-clothing'],
                ['name' => "Women's Clothing", 'slug' => 'womens-clothing'],
                ['name' => 'Footwear', 'slug' => 'footwear'],
            ]],
            ['name' => 'Home & Living', 'slug' => 'home-living', 'children' => [
                ['name' => 'Kitchen', 'slug' => 'kitchen'],
                ['name' => 'Furniture', 'slug' => 'furniture'],
            ]],
            ['name' => 'Sports', 'slug' => 'sports', 'children' => [
                ['name' => 'Cricket', 'slug' => 'cricket'],
                ['name' => 'Fitness', 'slug' => 'fitness'],
            ]],
        ];

        foreach ($categoryData as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);
            $parent = Category::create(array_merge($catData, ['is_active' => true]));
            foreach ($children as $child) {
                Category::create(array_merge($child, ['parent_id' => $parent->id, 'is_active' => true]));
            }
        }

        $mobilesCategory = Category::where('slug', 'mobile-phones')->first();
        $mensCategory    = Category::where('slug', 'mens-clothing')->first();
        $samsungBrand    = Brand::where('slug', 'samsung')->first();
        $appleBrand      = Brand::where('slug', 'apple')->first();
        $nikeBrand       = Brand::where('slug', 'nike')->first();
        $gulAhmedBrand   = Brand::where('slug', 'gul-ahmed')->first();

        $products = [
            ['name' => 'Samsung Galaxy S25',        'price' => 145000, 'sale_price' => 139999, 'category_id' => $mobilesCategory->id, 'brand_id' => $samsungBrand->id,  'stock' => 50,  'is_featured' => true,  'thumbnail' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&q=80'],
            ['name' => 'Samsung Galaxy A55',         'price' => 75000,  'sale_price' => null,   'category_id' => $mobilesCategory->id, 'brand_id' => $samsungBrand->id,  'stock' => 30,  'is_featured' => true,  'thumbnail' => 'https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=600&q=80'],
            ['name' => 'iPhone 16 Pro Max',          'price' => 280000, 'sale_price' => 270000, 'category_id' => $mobilesCategory->id, 'brand_id' => $appleBrand->id,   'stock' => 20,  'is_featured' => true,  'thumbnail' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&q=80'],
            ['name' => 'iPhone 15',                  'price' => 195000, 'sale_price' => null,   'category_id' => $mobilesCategory->id, 'brand_id' => $appleBrand->id,   'stock' => 15,  'is_featured' => false, 'thumbnail' => 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600&q=80'],
            ['name' => 'Nike Air Max 270',           'price' => 18000,  'sale_price' => 15000,  'category_id' => $mensCategory->id,    'brand_id' => $nikeBrand->id,    'stock' => 100, 'is_featured' => true,  'thumbnail' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80'],
            ['name' => 'Gul Ahmed Summer Suit',      'price' => 3500,   'sale_price' => 2999,   'category_id' => $mensCategory->id,    'brand_id' => $gulAhmedBrand->id,'stock' => 200, 'is_featured' => true,  'thumbnail' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80'],
            ['name' => 'Gul Ahmed Lawn Collection',  'price' => 2800,   'sale_price' => null,   'category_id' => $mensCategory->id,    'brand_id' => $gulAhmedBrand->id,'stock' => 150, 'is_featured' => false, 'thumbnail' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=600&q=80'],
            ['name' => 'Samsung Galaxy Buds2',       'price' => 15000,  'sale_price' => 12000,  'category_id' => $mobilesCategory->id, 'brand_id' => $samsungBrand->id,  'stock' => 75,  'is_featured' => true,  'thumbnail' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&q=80'],
        ];

        foreach ($products as $product) {
            $product['slug']              = Str::slug($product['name']) . '-' . uniqid();
            $product['is_active']         = true;
            $product['short_description'] = 'High quality ' . $product['name'] . ' available at the best price in Pakistan.';
            $product['description']       = '<p>Experience the best quality with ' . $product['name'] . '. Designed for the modern Pakistani consumer with premium features and unbeatable value.</p>';
            Product::create($product);
        }

        $banners = [
            ['title' => 'Summer Sale', 'subtitle' => 'Up to 50% off on all electronics', 'image' => 'https://placehold.co/1200x450/FF6B35/white?text=Summer+Sale', 'button_text' => 'Shop Now', 'link' => '/products?category=electronics', 'sort_order' => 1],
            ['title' => 'New Fashion Arrivals', 'subtitle' => 'Latest collection from top brands', 'image' => 'https://placehold.co/1200x450/4CAF50/white?text=New+Fashion', 'button_text' => 'Explore', 'link' => '/products?category=fashion', 'sort_order' => 2],
            ['title' => 'Free Delivery', 'subtitle' => 'On all orders above Rs. 2000', 'image' => 'https://placehold.co/1200x450/2196F3/white?text=Free+Delivery', 'button_text' => 'Shop Now', 'link' => '/products', 'sort_order' => 3],
        ];

        foreach ($banners as $banner) {
            Banner::create(array_merge($banner, ['is_active' => true]));
        }
    }
}
