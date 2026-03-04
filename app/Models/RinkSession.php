<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RinkSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'rink_id',
        'date',
        'start_time',
        'end_time',
        'session_type',
        'notes',
        'is_cancelled',
        'scraped_at',
    ];

    protected $casts = [
        'date' => 'date',
        'is_cancelled' => 'boolean',
        'scraped_at' => 'datetime',
    ];

    public function rink()
    {
        return $this->belongsTo(Rink::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
}
