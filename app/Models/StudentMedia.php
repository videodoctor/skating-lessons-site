<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentMedia extends Model
{
    protected $table = 'student_media';

    protected $fillable = [
        'student_id', 'type', 'path', 'original_path', 'thumbnail_path',
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

    public function getIsEditedAttribute(): bool
    {
        return !empty($this->original_path);
    }

    public function getOriginalUrlAttribute(): ?string
    {
        if (!$this->original_path) return null;
        return self::mediaUrl($this->original_path);
    }

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

    public function versions()
    {
        return $this->hasMany(MediaVersion::class, 'student_media_id')->orderBy('version');
    }

    public function latestVersion()
    {
        return $this->hasOne(MediaVersion::class, 'student_media_id')->latestOfMany('version');
    }

    public function addVersion(string $path, string $editType, ?array $editParams = null, ?array $meta = []): MediaVersion
    {
        $nextVersion = ($this->versions()->max('version') ?? 0) + 1;
        return MediaVersion::create([
            'student_media_id' => $this->id,
            'version'          => $nextVersion,
            'path'             => $path,
            'edit_type'        => $editType,
            'edit_params'      => $editParams,
            'file_size'        => $meta['file_size'] ?? null,
            'width'            => $meta['width'] ?? null,
            'height'           => $meta['height'] ?? null,
            'duration'         => $meta['duration'] ?? null,
            'created_by_type'  => $meta['created_by_type'] ?? 'admin',
            'created_by_id'    => $meta['created_by_id'] ?? null,
        ]);
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
        try {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(15));
        } catch (\Throwable $e) {
            // Fallback to direct S3 URL if credentials unavailable (dev without active SSO session)
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');
            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
        }
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
