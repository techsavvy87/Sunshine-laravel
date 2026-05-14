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
        $metadata = $this->metadata;

        if (is_string($metadata) && trim($metadata) !== '') {
            $decodedMetadata = json_decode($metadata, true);

            if (is_string($decodedMetadata) && trim($decodedMetadata) !== '') {
                $decodedMetadata = json_decode($decodedMetadata, true);
            }

            if (is_array($decodedMetadata)) {
                $metadata = $decodedMetadata;
            }
        }

        $metadata = is_array($metadata) ? $metadata : [];

        $petIds = $metadata['family_pet_ids'] ?? ($metadata['family_pets'] ?? ($metadata['pet_ids'] ?? []));

        if (is_array($petIds) && !empty($petIds) && is_array($petIds[0] ?? null)) {
            $petIds = collect($petIds)
                ->map(fn ($pet) => $pet['id'] ?? null)
                ->filter()
                ->values()
                ->all();
        }

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

    public function getAdditionalServicesByPetAttribute(): array
    {
        $metadata = $this->metadata;

        if (is_string($metadata) && trim($metadata) !== '') {
            $decodedMetadata = json_decode($metadata, true);

            if (is_string($decodedMetadata) && trim($decodedMetadata) !== '') {
                $decodedMetadata = json_decode($decodedMetadata, true);
            }

            if (is_array($decodedMetadata)) {
                $metadata = $decodedMetadata;
            }
        }

        $metadata = is_array($metadata) ? $metadata : [];
        $petIds = collect($this->family_pet_ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($petIds->isEmpty() && $this->pet_id) {
            $petIds = collect([(int) $this->pet_id]);
        }

        $normalizedMap = [];
        $rawMap = $metadata['additional_services_by_pet'] ?? [];

        if (is_array($rawMap)) {
            foreach ($rawMap as $petId => $serviceIds) {
                $normalizedPetId = (int) $petId;
                if ($normalizedPetId <= 0) {
                    continue;
                }

                if (is_string($serviceIds)) {
                    $serviceIds = explode(',', $serviceIds);
                }

                $normalizedServiceIds = collect(is_array($serviceIds) ? $serviceIds : [])
                    ->map(fn ($serviceId) => (int) $serviceId)
                    ->filter(fn ($serviceId) => $serviceId > 0)
                    ->unique()
                    ->values()
                    ->all();

                $normalizedMap[$normalizedPetId] = $normalizedServiceIds;
            }
        }

        if (empty($normalizedMap) && !empty($this->additional_service_ids)) {
            $legacyServiceIds = collect(explode(',', (string) $this->additional_service_ids))
                ->map(fn ($serviceId) => (int) trim($serviceId))
                ->filter(fn ($serviceId) => $serviceId > 0)
                ->unique()
                ->values()
                ->all();

            if (!empty($legacyServiceIds)) {
                if ($petIds->count() > 1) {
                    foreach ($petIds as $petId) {
                        $normalizedMap[(int) $petId] = $legacyServiceIds;
                    }
                } else {
                    $singlePetId = (int) ($petIds->first() ?? $this->pet_id);
                    if ($singlePetId > 0) {
                        $normalizedMap[$singlePetId] = $legacyServiceIds;
                    }
                }
            }
        }

        if ($petIds->isNotEmpty()) {
            $orderedMap = [];

            foreach ($petIds as $petId) {
                $orderedMap[(int) $petId] = $normalizedMap[(int) $petId] ?? [];
            }

            return $orderedMap;
        }

        ksort($normalizedMap);

        return $normalizedMap;
    }

    public function getAdditionalServiceIdsFlatAttribute(): array
    {
        return collect($this->additional_services_by_pet)
            ->flatten()
            ->map(fn ($serviceId) => (int) $serviceId)
            ->filter(fn ($serviceId) => $serviceId > 0)
            ->unique()
            ->values()
            ->all();
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
