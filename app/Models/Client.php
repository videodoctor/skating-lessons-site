<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'first_name', 'last_name',
        'email', 'password', 'phone', 'notes',
        'email_consent_at',
        'sms_consent', 'sms_phone',
        'email_verify_token', 'email_verified_at',
        'phone_verify_code', 'phone_verify_sent_at', 'phone_verified_at',
        'waiver_signed_at', 'waiver_version', 'waiver_ip',
        'access_token',
    ];

    protected $hidden = ['password', 'remember_token', 'phone_verify_code'];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'email_consent_at'     => 'datetime',
        'waiver_signed_at'     => 'datetime',
        'phone_verified_at'    => 'datetime',
        'phone_verify_sent_at' => 'datetime',
        'sms_consent'          => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function activeStudents()
    {
        return $this->hasMany(Student::class)->where('is_active', true);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function waivers()
    {
        return $this->hasMany(LiabilityWaiver::class);
    }

    public function latestWaiver()
    {
        return $this->hasOne(LiabilityWaiver::class)->latestOfMany();
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }
        return $this->name ?? '';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->email;
    }

    // ── Waiver helpers ─────────────────────────────────────────────────────────

    public function hasSignedCurrentWaiver(): bool
    {
        return LiabilityWaiver::clientHasValidWaiver($this->id);
    }

    public function waiverRequired(): bool
    {
        return !$this->hasSignedCurrentWaiver();
    }

    // ── Sync name fields ───────────────────────────────────────────────────────

    public static function boot()
    {
        parent::boot();

        static::saving(function ($client) {
            // Keep legacy name field in sync
            if ($client->first_name || $client->last_name) {
                $client->name = trim("{$client->first_name} {$client->last_name}");
            } elseif ($client->name && !$client->first_name) {
                $parts = explode(' ', $client->name, 2);
                $client->first_name = $parts[0];
                $client->last_name  = $parts[1] ?? null;
            }
        });
    }
}
