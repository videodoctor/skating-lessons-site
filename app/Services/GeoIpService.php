<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoIpService
{
    /**
     * Look up geo info for an IP address.
     * Returns ['country' => 'US', 'region' => 'Missouri', 'city' => 'St. Louis'] or nulls.
     */
    public function lookup(string $ip): array
    {
        $default = ['country' => null, 'region' => null, 'city' => null];

        // Skip private/local IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '10.') || str_starts_with($ip, '192.168.')) {
            return $default;
        }

        return Cache::remember("geoip:{$ip}", 86400, function () use ($ip, $default) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,countryCode,regionName,city',
                ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    return [
                        'country' => $response->json('countryCode'),
                        'region'  => $response->json('regionName'),
                        'city'    => $response->json('city'),
                    ];
                }
            } catch (\Throwable $e) {
                // Silently fail — geo data is nice-to-have
            }

            return $default;
        });
    }
}
