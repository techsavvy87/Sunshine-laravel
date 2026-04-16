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
}
