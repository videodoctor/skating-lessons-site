<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip_address', 'path', 'referrer_url', 'referrer_source',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'ref_tag',
        'country', 'region', 'city', 'user_agent', 'client_id', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeHomepage($query)
    {
        return $query->where('path', '/');
    }

    /**
     * Classify a referrer URL into a human-readable source.
     */
    public static function classifyReferrer(?string $url): string
    {
        if (!$url) return 'direct';

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if (!$host) return 'direct';

        // Strip www.
        $host = preg_replace('/^www\./', '', $host);

        $map = [
            'google'    => ['google.com', 'google.co'],
            'bing'      => ['bing.com'],
            'yahoo'     => ['yahoo.com', 'search.yahoo'],
            'instagram' => ['instagram.com', 'l.instagram.com'],
            'facebook'  => ['facebook.com', 'l.facebook.com', 'lm.facebook.com', 'm.facebook.com'],
            'tiktok'    => ['tiktok.com'],
            'twitter'   => ['twitter.com', 't.co', 'x.com'],
            'youtube'   => ['youtube.com', 'youtu.be'],
            'nextdoor'  => ['nextdoor.com'],
        ];

        foreach ($map as $source => $domains) {
            foreach ($domains as $domain) {
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    return $source;
                }
            }
        }

        // Check if it's our own site
        if (str_contains($host, 'kristineskates.com')) {
            return 'internal';
        }

        return 'other';
    }
}
