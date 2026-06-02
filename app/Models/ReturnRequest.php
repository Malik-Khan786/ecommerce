<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'description',
        'status',
        'admin_notes',
        'uuid',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ReturnRequest $returnRequest) {
            $returnRequest->uuid = (string) Str::uuid();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
