<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $table = 'order_tracking';

    protected $fillable = [
        'order_id',
        'status',
        'title',
        'description',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
