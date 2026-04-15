<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceIssue extends Model
{
    protected $fillable = [
        'type',
        'description',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
