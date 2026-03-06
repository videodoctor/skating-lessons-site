<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannerScan extends Model
{
    protected $fillable = [
        'month', 'year', 'image_paths',
        'entries_extracted', 'entries_confirmed',
        'is_finalized', 'scanned_by',
    ];

    protected $casts = [
        'image_paths'  => 'array',
        'is_finalized' => 'boolean',
    ];

    public function entries()
    {
        return $this->hasMany(PlannerScanEntry::class);
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function privateEntries()
    {
        return $this->entries()->where('type', 'private_lesson');
    }
}
