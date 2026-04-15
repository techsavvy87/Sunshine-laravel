<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetVaccinationAlert extends Model
{
    public const TYPE_WARNING = 'warning';
    public const TYPE_EXPIRED = 'expired';

    protected $fillable = [
        'pet_vaccination_id',
        'pet_profile_id',
        'alert_type',
        'expires_on',
    ];

    protected $casts = [
        'expires_on' => 'date',
        'email_sent_at' => 'datetime',
        'in_app_sent_at' => 'datetime',
    ];

    public function pet()
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id', 'id');
    }

    public function vaccination()
    {
        return $this->belongsTo(PetVaccination::class, 'pet_vaccination_id', 'id');
    }
}