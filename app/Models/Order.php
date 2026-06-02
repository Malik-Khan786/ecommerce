<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'uuid', 'user_id', 'guest_name', 'guest_email', 'guest_phone',
        'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city',
        'shipping_state', 'shipping_postal_code', 'shipping_country',
        'subtotal', 'shipping_cost', 'discount', 'total',
        'payment_method', 'payment_status', 'status', 'notes',
        'ack_token', 'ack_status', 'ack_message', 'ack_at',
        'estimated_delivery_date',
    ];

    protected $casts = [
        'subtotal'                => 'decimal:2',
        'shipping_cost'           => 'decimal:2',
        'discount'                => 'decimal:2',
        'total'                   => 'decimal:2',
        'ack_at'                  => 'datetime',
        'estimated_delivery_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->uuid         = (string) Str::uuid();
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }

    public function generateAckToken(): string
    {
        $token = Str::random(48);
        $this->update(['ack_token' => $token]);
        return $token;
    }

    public function user()          { return $this->belongsTo(User::class); }
    public function items()         { return $this->hasMany(OrderItem::class); }
    public function tracking()      { return $this->hasMany(OrderTracking::class); }
    public function returnRequests() { return $this->hasMany(ReturnRequest::class); }
}
