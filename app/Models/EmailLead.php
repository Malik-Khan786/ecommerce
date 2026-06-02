<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLead extends Model
{
    protected $fillable = [
        'email', 'name', 'product_id', 'source',
        'ip_address', 'browser', 'device', 'is_contacted', 'notes', 'notified_at',
    ];

    protected $casts = [
        'is_contacted' => 'boolean',
        'notified_at'  => 'datetime',
    ];

    public function product() { return $this->belongsTo(Product::class); }
}
