<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendance';

    protected $fillable = [
        'user_id',
        'date',
        'present',
        'injury_sickness',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'present' => 'boolean',
            'injury_sickness' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
