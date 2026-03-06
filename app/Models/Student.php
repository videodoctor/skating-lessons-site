<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'client_id', 'first_name', 'last_name', 'age',
        'skill_level', 'notes', 'is_active', 'waiver_signed',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'waiver_signed' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function aliases()
    {
        return $this->hasMany(StudentAlias::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function plannerScanEntries()
    {
        return $this->hasMany(PlannerScanEntry::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->first_name . ($this->last_name ? ' ' . $this->last_name : '');
    }

    /**
     * Find a student by alias (fuzzy match).
     * Returns best match or null.
     */
    public static function findByAlias(string $name): ?self
    {
        $name = trim($name);

        // Exact alias match
        $alias = StudentAlias::where('alias', $name)->with('student')->first();
        if ($alias) return $alias->student;

        // Exact first name match
        $student = self::where('first_name', $name)->where('is_active', true)->first();
        if ($student) return $student;

        // Case-insensitive first name
        $student = self::whereRaw('LOWER(first_name) = ?', [strtolower($name)])->where('is_active', true)->first();
        if ($student) return $student;

        // Partial match — name contains first_name or first_name contains name
        $student = self::where('is_active', true)
            ->where(function ($q) use ($name) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($name) . '%'])
                  ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(first_name), "%")', [$name]);
            })
            ->first();

        return $student;
    }

    /**
     * Similarity score (0-100) between this student's name and a given string.
     */
    public function similarityScore(string $name): int
    {
        $name = strtolower(trim($name));
        $full = strtolower($this->full_name);
        $first = strtolower($this->first_name);

        if ($name === $full) return 100;
        if ($name === $first) return 90;

        // Check aliases
        foreach ($this->aliases as $alias) {
            if (strtolower($alias->alias) === $name) return 95;
        }

        // Levenshtein distance
        $distFull  = levenshtein($name, $full);
        $distFirst = levenshtein($name, $first);
        $dist      = min($distFull, $distFirst);
        $maxLen    = max(strlen($name), strlen($full));

        if ($maxLen === 0) return 0;
        return max(0, 100 - (int)(($dist / $maxLen) * 100));
    }
}
