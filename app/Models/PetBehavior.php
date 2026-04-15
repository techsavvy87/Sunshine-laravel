<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetBehavior extends Model
{
    protected $fillable = [
        'icon_id',
        'description',
    ];

    public function icon()
    {
        return $this->belongsTo(BehaviorIcon::class, 'icon_id');
    }
}
