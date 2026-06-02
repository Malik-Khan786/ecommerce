<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'user_id', 'guest_token', 'email',
        'name', 'ip_address', 'browser', 'device', 'platform', 'viewed_at',
    ];

    protected $casts = ['viewed_at' => 'datetime'];

    public function product() { return $this->belongsTo(Product::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
