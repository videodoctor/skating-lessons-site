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
        $cdn = config('services.media_cdn_url');
        return "{$cdn}/{$this->path}";
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) return null;
        $cdn = config('services.media_cdn_url');
        return "{$cdn}/{$this->thumbnail_path}";
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
