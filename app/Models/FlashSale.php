<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSale extends Model
{
    protected $fillable = [
        'product_id',
        'discount_percent',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'starts_at'        => 'datetime',
            'ends_at'          => 'datetime',
            'is_active'        => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('starts_at', '<=', now())
                     ->where('ends_at', '>=', now());
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
