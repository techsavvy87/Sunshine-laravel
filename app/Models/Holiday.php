<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'fixed_price',
        'application_type',
        'end_date',
        'restrict_bookings',
    ];

    protected $casts = [
        'date' => 'date',
        'fixed_price' => 'decimal:2',
        'application_type' => 'string',
        'end_date' => 'date',
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
