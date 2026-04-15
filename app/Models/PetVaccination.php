<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetVaccination extends Model
{
    protected $casts = [
        'date' => 'date',
    ];

    public function pet()
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id', 'id');
    }

    public function alerts()
    {
        return $this->hasMany(PetVaccinationAlert::class, 'pet_vaccination_id', 'id');
    }
}
