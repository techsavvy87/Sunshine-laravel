<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Room;

class Appointment extends Model
{
    protected $casts = [
        'metadata' => 'array'
    ];

    public function getFamilyPetIdsAttribute(): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $petIds = $metadata['family_pet_ids'] ?? [];

        if (is_string($petIds)) {
            $petIds = explode(',', $petIds);
        }

        $ids = collect(is_array($petIds) ? $petIds : [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($ids->isEmpty() && $this->pet_id) {
            $ids = collect([(int) $this->pet_id]);
        }

        return $ids->all();
    }

    public function getFamilyPetsAttribute()
    {
        $petIds = $this->family_pet_ids;

        if (empty($petIds)) {
            return collect();
        }

        $pets = PetProfile::whereIn('id', $petIds)->get()->keyBy('id');

        return collect($petIds)
            ->map(fn ($id) => $pets->get((int) $id))
            ->filter()
            ->values();
    }

    public function pet()
    {
        return $this->belongsTo(PetProfile::class, 'pet_id', 'id');
    }

    public function kennel()
    {
        return $this->belongsTo(Kennel::class, 'kennel_id', 'id');
    }

    public function catRoom()
    {
        return $this->belongsTo(Room::class, 'cat_room_id', 'id');
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
