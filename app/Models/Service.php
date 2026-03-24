<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'duration_minutes',
        'price',
        'features',
        'is_active',
        'coming_soon',
        'coming_soon_teaser',
        'show_price',
        'show_duration',
        'show_features',
        'show_description',
        'discount_amount',
        'discount_type',
        'discount_starts_at',
        'discount_ends_at',
    ];

    protected $casts = [
        'features'         => 'array',
        'price'            => 'decimal:2',
        'is_active'        => 'boolean',
        'coming_soon'      => 'boolean',
        'show_price'       => 'boolean',
        'show_duration'    => 'boolean',
        'show_features'    => 'boolean',
        'show_description' => 'boolean',
        'discount_amount'    => 'decimal:2',
        'discount_starts_at' => 'date',
        'discount_ends_at'   => 'date',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ── Discount helpers ───────────────────────────────────────────────────────

    public function hasActiveDiscount(): bool
    {
        if (!$this->discount_amount || !$this->discount_type) return false;

        $now = Carbon::today();

        if ($this->discount_starts_at && $now->lt($this->discount_starts_at)) return false;
        if ($this->discount_ends_at && $now->gt($this->discount_ends_at)) return false;

        return true;
    }

    public function discountedPrice(): float
    {
        if (!$this->hasActiveDiscount()) return (float) $this->price;

        if ($this->discount_type === 'percent') {
            return round((float) $this->price * (1 - $this->discount_amount / 100), 2);
        }

        return max(0, (float) $this->price - (float) $this->discount_amount);
    }

    public function discountLabel(): ?string
    {
        if (!$this->hasActiveDiscount()) return null;

        if ($this->discount_type === 'percent') {
            return number_format($this->discount_amount, 0) . '% off';
        }

        return '$' . number_format($this->discount_amount, 0) . ' off';
    }

    public function effectivePrice(): float
    {
        return $this->hasActiveDiscount() ? $this->discountedPrice() : (float) $this->price;
    }
}
