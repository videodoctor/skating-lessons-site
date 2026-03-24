<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceWaitlist extends Model
{
    protected $table = 'service_waitlist';

    protected $fillable = ['service_id', 'email', 'name'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
