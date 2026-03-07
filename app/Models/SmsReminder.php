<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsReminder extends Model
{
    protected $fillable = [
        'booking_id', 'client_id', 'to_number', 'message_body',
        'twilio_sid', 'status', 'reply', 'sent_at', 'replied_at', 'scheduled_for',
    ];

    protected $casts = [
        'sent_at'       => 'datetime',
        'replied_at'    => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isConfirmed(): bool
    {
        return strtoupper(trim($this->reply ?? '')) === 'YES';
    }

    public function isCancelled(): bool
    {
        return strtoupper(trim($this->reply ?? '')) === 'NO';
    }
}
