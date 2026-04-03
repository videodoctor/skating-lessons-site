<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentMedia extends Model
{
    protected $table = 'student_media';

    protected $fillable = [
        'student_id', 'type', 'path', 'thumbnail_path',
        'original_filename', 'mime_type', 'file_size',
        'width', 'height', 'duration', 'media_metadata',
        'caption', 'is_profile_photo',
        'uploaded_by_type', 'uploaded_by_id',
    ];

    protected $casts = [
        'is_profile_photo' => 'boolean',
        'file_size'        => 'integer',
        'width'            => 'integer',
        'height'           => 'integer',
        'duration'         => 'float',
        'media_metadata'   => 'array',
    ];

    public function getAspectRatioAttribute(): float
    {
        if ($this->width && $this->height) {
            return $this->width / $this->height;
        }
        return 16 / 9; // default fallback
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getUrlAttribute(): string
    {
        return self::mediaUrl($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) return null;
        return self::mediaUrl($this->thumbnail_path);
    }

    /**
     * Generate a URL for a media path.
     * Prod: CloudFront CDN (origin path strips prefix from URL).
     * Dev/Staging: Signed S3 URL (temporary, direct).
     */
    public static function mediaUrl(string $path): string
    {
        $prefix = config('services.media_path_prefix');

        if (!$prefix || $prefix === 'prod') {
            // Prod: CloudFront URL, strip the prefix from the path since origin path handles it
            $cdn = config('services.media_cdn_url');
            $cleanPath = $prefix ? preg_replace('#^' . preg_quote($prefix . '/', '#') . '#', '', $path) : $path;
            return "{$cdn}/{$cleanPath}";
        }

        // Dev/Staging: signed S3 URL (15 min expiry)
        return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(15));
    }

    public function scopePhotos($query)
    {
        return $query->where('type', 'photo');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }
}
