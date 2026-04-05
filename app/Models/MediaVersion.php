<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaVersion extends Model
{
    protected $fillable = [
        'student_media_id', 'version', 'path', 'edit_type', 'edit_params',
        'file_size', 'width', 'height', 'duration',
        'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'edit_params' => 'array',
        'file_size'   => 'integer',
        'width'       => 'integer',
        'height'      => 'integer',
        'duration'    => 'float',
    ];

    public function media()
    {
        return $this->belongsTo(StudentMedia::class, 'student_media_id');
    }

    public function getUrlAttribute(): string
    {
        return StudentMedia::mediaUrl($this->path);
    }
}
