<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLink extends Model
{
    protected $fillable = [
        'invoice_id',
        'appointment_id',
        'secure_token',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'expires_at',
        'completed_at',
        'payment_method',
        'stripe_transaction_id',
        'error_message',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function isExpired()
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
