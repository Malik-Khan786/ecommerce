<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'short_description',
        'description', 'price', 'sale_price', 'stock', 'sku',
        'thumbnail', 'is_active', 'is_featured', 'scheduled_at',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'sale_price'   => 'decimal:2',
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function images() { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function variants() { return $this->hasMany(ProductVariant::class)->orderBy('sort_order'); }
    public function stockAlerts() { return $this->hasMany(StockAlert::class); }
    public function questions() { return $this->hasMany(ProductQuestion::class); }
}
