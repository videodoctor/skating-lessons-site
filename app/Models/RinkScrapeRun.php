<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RinkScrapeRun extends Model
{
    protected $fillable = [
        'rink_id', 'month', 'year', 'source_file_path', 'source_type',
        'source_url', 'sessions_found', 'sessions_added', 'sessions_removed',
        'scrape_log', 'had_errors', 'scraped_at',
    ];

    protected $casts = [
        'scraped_at' => 'datetime',
        'had_errors' => 'boolean',
    ];

    public function rink()
    {
        return $this->belongsTo(Rink::class);
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function hasSourceFile(): bool
    {
        return $this->source_file_path && \Storage::exists($this->source_file_path);
    }
}
