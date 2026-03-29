<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'action', 'description', 'metadata', 'ip_address', 'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
