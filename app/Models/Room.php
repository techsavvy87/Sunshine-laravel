<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'img',
        'name',
        'description',
        'kennel_ids',
        'capacity',
        'room_types',
        'space_option',
        'restrict_count',
        'pet_type_labels',
        'type',
        'status',
    ];

    public function getKennelIdArrayAttribute(): array
    {
        if (empty($this->kennel_ids)) {
            return [];
        }

        return collect(explode(',', $this->kennel_ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    public function getRoomTypeArrayAttribute(): array
    {
        if (empty($this->room_types)) {
            return [];
        }

        return collect(explode(',', $this->room_types))
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter(fn ($value) => in_array($value, ['standard', 'space']))
            ->unique()
            ->values()
            ->all();
    }

    public function getPetTypeLabelArrayAttribute(): array
    {
        if (empty($this->pet_type_labels)) {
            return [];
        }

        return collect(explode(',', $this->pet_type_labels))
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter(fn ($value) => in_array($value, ['dog', 'cat']))
            ->unique()
            ->values()
            ->all();
    }
}
