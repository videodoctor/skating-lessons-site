<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenmoPayment extends Model
{
    protected $fillable = [
        'transaction_id', 'sender_name', 'amount', 'note',
        'paid_at', 'booking_id', 'client_id', 'match_status', 'raw_subject',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isMatched(): bool
    {
        return $this->match_status === 'matched';
    }

    public function isUnmatched(): bool
    {
        return $this->match_status === 'unmatched';
    }
}
