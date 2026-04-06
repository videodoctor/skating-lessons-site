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
        'email_consent_at', 'terms_accepted_at', 'must_accept_terms',
        'sms_consent', 'sms_phone', 'notification_prefs',
        'last_login_at',
        'email_verify_token', 'email_verified_at',
        'phone_verify_code', 'phone_verify_sent_at', 'phone_verified_at',
        'calendar_token',
        'waiver_signed_at', 'waiver_version', 'waiver_ip',
        'access_token',
        'referral_source', 'utm_source', 'utm_medium', 'utm_campaign',
    ];

    protected $hidden = ['password', 'remember_token', 'phone_verify_code'];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'email_consent_at'     => 'datetime',
        'terms_accepted_at'    => 'datetime',
        'must_accept_terms'    => 'boolean',
        'waiver_signed_at'     => 'datetime',
        'last_login_at'        => 'datetime',
        'notification_prefs'   => 'array',
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

    // ── Notification preferences ──────────────────────────────────────────

    const NOTIFICATION_CATEGORIES = [
        'lesson_reminders'    => 'Lesson Reminders',
        'booking_updates'     => 'Booking Confirmations & Updates',
        'schedule_changes'    => 'Schedule Changes & Cancellations',
        'availability'        => 'Availability & Waitlist Notifications',
        'public_skate_times'  => 'Public Skate Schedules',
        'account_security'    => 'Account Security & Verification',
    ];

    public function notifPref(string $category, string $channel): bool
    {
        $prefs = $this->notification_prefs ?? [];

        // Account security is always on for both channels
        if ($category === 'account_security') return true;

        // Default: email on for all if consented, SMS on for reminders/bookings/security if consented
        $defaults = [
            'lesson_reminders'   => ['email' => true, 'sms' => true],
            'booking_updates'    => ['email' => true, 'sms' => true],
            'schedule_changes'   => ['email' => true, 'sms' => true],
            'availability'       => ['email' => true, 'sms' => false],
            'public_skate_times' => ['email' => false, 'sms' => false],
            'account_security'   => ['email' => true, 'sms' => true],
        ];

        // If no prefs set, use defaults gated by consent
        if (!isset($prefs[$category][$channel])) {
            $default = $defaults[$category][$channel] ?? false;
            if ($channel === 'sms') return $default && $this->sms_consent;
            if ($channel === 'email') return $default && $this->email_consent_at;
            return false;
        }

        // Explicit pref, but still gated by consent
        if ($channel === 'sms' && !$this->sms_consent) return false;
        if ($channel === 'email' && !$this->email_consent_at) return false;

        return (bool) $prefs[$category][$channel];
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

        static::creating(function ($client) {
            if (!$client->calendar_token) {
                $client->calendar_token = bin2hex(random_bytes(24));
            }
        });

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
