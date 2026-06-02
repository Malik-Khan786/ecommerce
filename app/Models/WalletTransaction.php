<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public $timestamps   = false;
    const CREATED_AT     = 'created_at';
    const UPDATED_AT     = null;

    protected $fillable = [
        'user_id', 'amount', 'type', 'description', 'reference_type', 'reference_id',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
