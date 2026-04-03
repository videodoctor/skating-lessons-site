<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'student_id',
        'client_name',
        'client_email',
        'client_phone',
        'service_id',
        'time_slot_id',
        'suggested_time_slot_id',
        'suggestion_message',
        'suggestion_token',
        'suggestion_sent_at',
        'suggestion_responded_at',
        'student_age',
        'student_name',
        'skill_level',
        'date',
        'start_time',
        'end_time',
        'status',
        'payment_method',
        'payment_type',
        'payment_status',
        'venmo_transaction_id',
        'venmo_username',
        'venmo_confirmed_at',
        'cash_paid_at',
        'cash_marked_by',
        'price_paid',
        'notes',
        'referred_by',
        'cancellation_reason',
        'confirmation_code',
        'email_consent_at',
        'guest_sms_consent',
        'guest_convert_token',
    ];

    protected $casts = [
        'date'               => 'date',
        'price_paid'         => 'decimal:2',
        'venmo_confirmed_at' => 'datetime',
        'cash_paid_at'       => 'datetime',
        'email_consent_at'   => 'datetime',
        'guest_sms_consent'  => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            if (!$booking->confirmation_code) {
                $booking->confirmation_code = strtoupper(substr(md5(uniqid()), 0, 8));
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function suggestedTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'suggested_time_slot_id');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(Assessment::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed', 'paid']);
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isUpcoming(): bool
    {
        return $this->date >= today() && in_array($this->status, ['pending', 'confirmed', 'paid']);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->client_name && trim($this->client_name) !== '') {
            return $this->client_name;
        }
        if ($this->student) {
            return $this->student->full_name;
        }
        if ($this->client) {
            return $this->client->full_name;
        }
        return $this->client_email ?: 'Unknown';
    }

    public function getVenmoLinkAttribute(): string
    {
        $handle  = ltrim(config('services.venmo.handle', 'Kristine-Humphrey'), '@');
        $amount  = number_format($this->price_paid, 2);
        $note    = urlencode('Skating Lesson ' . $this->confirmation_code);
        return "venmo://paycharge?txn=pay&recipients={$handle}&amount={$amount}&note={$note}";
    }
}
