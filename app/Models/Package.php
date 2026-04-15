<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Service;

class Package extends Model
{
    protected $fillable = [
        'image',
        'name',
        'price',
        'description',
        'service_ids',
        'days',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'days' => 'integer',
    ];

    /**
     * Get the service IDs as an array
     */
    public function getServiceIdsArrayAttribute(): array
    {
        if (empty($this->service_ids)) {
            return [];
        }
        return array_map('trim', explode(',', $this->service_ids));
    }

    /**
     * Get the services associated with this package
     */
    public function getServicesAttribute()
    {
        $serviceIds = $this->service_ids_array;
        if (empty($serviceIds)) {
            return collect([]);
        }
        return Service::whereIn('id', $serviceIds)->get();
    }
}
