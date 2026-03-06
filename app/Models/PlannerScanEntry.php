<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannerScanEntry extends Model
{
    protected $fillable = [
        'planner_scan_id', 'date', 'time', 'raw_text', 'type',
        'rink', 'extracted_name', 'student_id', 'booking_id',
        'confidence', 'match_status', 'notes', 'confirmed_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'confirmed_at' => 'datetime',
        'confidence'   => 'integer',
    ];

    public function scan()
    {
        return $this->belongsTo(PlannerScan::class, 'planner_scan_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function isPrivateLesson(): bool
    {
        return $this->type === 'private_lesson';
    }

    public function needsReview(): bool
    {
        return $this->isPrivateLesson() && (
            $this->confidence < 80 ||
            $this->student_id === null ||
            $this->match_status === 'unmatched'
        );
    }
}
