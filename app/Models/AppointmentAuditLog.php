<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit log for the appointment lifecycle.
 * Stores snapshot data only: pet_name, owner_name, type, employee.
 * No actor_id or appointment_id - survives user/appointment deletion.
 */
class AppointmentAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'description',
        'pet_name',
        'pet_avatar',
        'owner_name',
        'type',
        'employee',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
