<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaJob extends Model
{
    protected $fillable = ['media_id', 'type', 'status', 'progress', 'message'];
}
