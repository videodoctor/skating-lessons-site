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
        'schedule_pdf_url',
        'schedule_format',
        'scraper_notes',
        'is_active',
        'is_displayed',
        'inactive_message',
        'last_scraped_at',
        'ocr_provider',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_displayed' => 'boolean',
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
