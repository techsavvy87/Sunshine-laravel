<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'percent_increase',
        'restrict_bookings',
    ];

    protected $casts = [
        'date' => 'date',
        'percent_increase' => 'integer',
    ];

    /**
     * Get the holiday services for the holiday.
     */
    public function holidayServices()
    {
        return $this->hasMany(HolidayService::class);
    }

    /**
     * Get the services for the holiday.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'holiday_services')
                    ->withPivot('max_value')
                    ->withTimestamps();
    }
}
