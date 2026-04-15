<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'service_id',
        'staff_id',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'booked_count',
        'pet_size',
        'daycare_type',
        'private_training_type',
        'status',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->booked_count < $this->capacity;
    }

    public function getRemainingCapacity(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function isFull(): bool
    {
        return $this->booked_count >= $this->capacity;
    }

    public function incrementBooking(): bool
    {
        if ($this->isAvailable()) {
            $this->increment('booked_count');

            if ($this->booked_count >= $this->capacity) {
                $this->update(['status' => 'full']);
            }

            return true;
        }

        return false;
    }

    public function decrementBooking(): void
    {
        if ($this->booked_count > 0) {
            $this->decrement('booked_count');

            // Update status to 'available' if there are available staff
            if ($this->booked_count < $this->capacity && $this->status === 'full') {
                $this->update(['status' => 'available']);
            }
        }
    }

    public function getDurationInMinutes(): int
    {
        if ($this->service && $this->pet_size) {
            switch ($this->pet_size) {
                case 'small':
                    return (int) round(($this->service->duration_small ?? 0) * 60);
                case 'medium':
                    return (int) round(($this->service->duration_medium ?? 0) * 60);
                case 'large':
                    return (int) round(($this->service->duration_large ?? 0) * 60);
            }
        }

        $start = \Carbon\Carbon::createFromTimeString($this->start_time);
        $end = \Carbon\Carbon::createFromTimeString($this->end_time);
        return $start->diffInMinutes($end);
    }

    public function scopeForPetSize($query, $petSize)
    {
        return $query->where('pet_size', $petSize);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->whereColumn('booked_count', '<', 'capacity');
    }
}
