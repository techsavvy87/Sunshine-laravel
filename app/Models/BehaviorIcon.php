<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehaviorIcon extends Model
{
    protected $fillable = [
        'icon',
    ];

    public function petBehaviors()
    {
        return $this->hasMany(PetBehavior::class, 'icon_id');
    }
}
