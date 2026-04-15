<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetInitialTemperament extends Model
{
    protected $table = 'pet_initial_temperaments';

    protected $fillable = [
        'pet_id',
        'temperament_data'
    ];

    protected $casts = [
        'temperament_data' => 'array'
    ];

    public function pet()
    {
        return $this->belongsTo(PetProfile::class, 'pet_id', 'id');
    }
}
