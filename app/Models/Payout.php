<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'amount',
        'currency',
        'stripe_payout_id',
        'status',
        'arrival_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'arrival_date' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
