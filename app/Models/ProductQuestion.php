<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuestion extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'guest_name', 'guest_email',
        'question', 'answer', 'is_answered', 'is_approved',
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
