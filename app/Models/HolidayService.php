<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayService extends Model
{
    protected $fillable = [
        'holiday_id',
        'service_id',
        'max_value',
    ];

    protected $casts = [
        'max_value' => 'integer',
    ];

    /**
     * Get the holiday that owns the holiday service.
     */
    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * Get the service that owns the holiday service.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
