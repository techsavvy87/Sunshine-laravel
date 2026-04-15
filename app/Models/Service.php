<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'price_small' => 'decimal:2',
        'price_medium' => 'decimal:2',
        'price_large' => 'decimal:2',
        'price_xlarge' => 'decimal:2',
        'price_per_mile' => 'decimal:2',
        'duration' => 'float',
        'duration_small' => 'float',
        'duration_medium' => 'float',
        'duration_large' => 'float',
        'duration_xlarge' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id', 'id');
    }

    /**
     * Get the holiday services for the service.
     */
    public function holidayServices()
    {
        return $this->hasMany(HolidayService::class);
    }

    /**
     * Get the holidays for the service.
     */
    public function holidays()
    {
        return $this->belongsToMany(Holiday::class, 'holiday_services')
                    ->withPivot('max_value')
                    ->withTimestamps();
    }

}
