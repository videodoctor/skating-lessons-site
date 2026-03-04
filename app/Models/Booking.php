<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

protected $fillable = [
    'client_id',
    'client_name',
    'client_email',
    'client_phone',
    'service_id',
    'time_slot_id',
    'student_age',
    'date',
    'start_time',
    'end_time',
    'status',
    'payment_method',
    'payment_status',
    'venmo_transaction_id',
    'price_paid',
    'notes',
    'cancellation_reason',
    'confirmation_code',
];

    protected $casts = [
        'date' => 'date',
        'price_paid' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->confirmation_code) {
                $booking->confirmation_code = strtoupper(substr(md5(uniqid()), 0, 8));
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(Assessment::class);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed', 'paid']);
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isUpcoming(): bool
    {
        return $this->date >= today() && in_array($this->status, ['pending', 'confirmed', 'paid']);
    }
}
