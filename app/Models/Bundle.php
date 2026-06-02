<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'discount_percent', 'is_active', 'image',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function bundleItems()
    {
        return $this->hasMany(BundleItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
