<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'website_url',
        'schedule_url',
        'schedule_format',
        'scraper_notes',
        'is_active',
        'last_scraped_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_scraped_at' => 'datetime',
    ];

    public function sessions()
    {
        return $this->hasMany(RinkSession::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function instructorPreferences()
    {
        return $this->hasMany(InstructorPreference::class);
    }
}
