<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComplaint extends Model
{
    protected $fillable = [
        'customer_id',
        'description',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
}
