<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $casts = [
        'metadata' => 'array'
    ];

    public function pet()
    {
        return $this->belongsTo(PetProfile::class, 'pet_id', 'id');
    }

    public function kennel()
    {
        return $this->belongsTo(Kennel::class, 'kennel_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'appointment_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'appointment_id', 'id');
    }

    public function checkin()
    {
        return $this->hasOne(Checkin::class, 'appointment_id', 'id');
    }

    public function process()
    {
        return $this->hasOne(Process::class, 'appointment_id', 'id');
    }

    public function checkout()
    {
        return $this->hasOne(Checkout::class, 'appointment_id', 'id');
    }

    public function cancellations()
    {
        return $this->hasMany(AppointmentCancellation::class, 'appointment_id', 'id');
    }
}
