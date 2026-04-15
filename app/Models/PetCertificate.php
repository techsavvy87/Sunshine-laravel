<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetCertificate extends Model
{
    public function pet()
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id', 'id');
    }
}
