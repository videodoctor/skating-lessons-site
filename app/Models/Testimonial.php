<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['quote', 'author', 'author_detail', 'source_type', 'is_active', 'sort_order', 'client_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public static function sourceTypes(): array
    {
        return [
            'hockey_parent' => 'Hockey Parent',
            'player'        => 'Player',
            'adult_skater'  => 'Adult Skater',
            'youth_skater'  => 'Youth Skater',
        ];
    }

    public function sourceLabel(): ?string
    {
        return self::sourceTypes()[$this->source_type] ?? null;
    }
}
