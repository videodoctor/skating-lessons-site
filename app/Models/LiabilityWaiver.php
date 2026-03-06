<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiabilityWaiver extends Model
{
    protected $fillable = [
        'client_id', 'waiver_version', 'waiver_text',
        'signed_name', 'ip_address', 'user_agent',
        'student_ids', 'signed_at',
    ];

    protected $casts = [
        'student_ids' => 'array',
        'signed_at'   => 'datetime',
    ];

    // Current waiver version — bump this when waiver text changes to require re-signing
    const CURRENT_VERSION = '1.0';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function students()
    {
        return Student::whereIn('id', $this->student_ids ?? [])->get();
    }

    public static function currentWaiverText(): string
    {
        return view('legal.waiver-text')->render();
    }

    public static function clientHasValidWaiver(int $clientId): bool
    {
        return self::where('client_id', $clientId)
            ->where('waiver_version', self::CURRENT_VERSION)
            ->exists();
    }
}
