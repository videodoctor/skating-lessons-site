<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingInterest extends Model
{
    protected $fillable = [
        'service_id', 'source',
        'name', 'email', 'phone', 'message',
        'student_name', 'student_age', 'skill_level',
        'email_consent', 'sms_consent',
        'waiver_accepted', 'terms_accepted',
    ];

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    protected $casts = [
        'email_consent'    => 'boolean',
        'sms_consent'      => 'boolean',
        'waiver_accepted'  => 'boolean',
        'terms_accepted'   => 'boolean',
    ];
}
