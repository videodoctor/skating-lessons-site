<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'rink_id',
        'rink_session_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_available',
        'booking_id',
        'priority',
        'is_pre_allocated',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function rink()
    {
        return $this->belongsTo(Rink::class);
    }
}
