<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityAddress extends Model
{
    protected $fillable = [
        'address',
        'city',
        'state',
        'zip_code',
    ];
}
