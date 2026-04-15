<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'amount',
        'service_ids',
        'customer_ids',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'service_ids' => 'array',
        'customer_ids' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}
