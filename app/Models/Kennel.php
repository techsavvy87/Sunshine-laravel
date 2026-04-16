<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kennel extends Model
{
    protected $fillable = [
        'img',
        'name',
        'description',
        'type',
        'capacity',
        'status',
    ];
}
