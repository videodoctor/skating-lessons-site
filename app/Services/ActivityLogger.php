<?php

namespace App\Services;

use App\Models\ClientActivityLog;

class ActivityLogger
{
    public static function log(int $clientId, string $action, ?string $description = null, ?array $metadata = null): void
    {
        ClientActivityLog::create([
            'client_id'   => $clientId,
            'action'      => $action,
            'description' => $description,
            'metadata'    => $metadata,
            'ip_address'  => request()->ip(),
            'created_at'  => now(),
        ]);
    }
}
