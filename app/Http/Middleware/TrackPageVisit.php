<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use App\Services\GeoIpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Runs after the response is sent to the browser — zero latency impact.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only track GET requests
        if ($request->method() !== 'GET') return;

        // Skip webhooks, debugbar, and asset paths (but allow admin paths for admin tracking)
        $path = $request->path();
        if (preg_match('#^(webhooks|_debugbar|build|images|fonts|favicon)#', $path)) return;

        // Skip bots
        $ua = $request->userAgent() ?? '';
        if (preg_match('/bot|crawl|spider|slurp|facebookexternalhit|Mediapartners/i', $ua)) return;

        // Persist UTM/ref params in session for attribution at registration
        if ($request->has('ref') || $request->has('utm_source')) {
            session([
                'analytics.ref'          => $request->get('ref', session('analytics.ref')),
                'analytics.utm_source'   => $request->get('utm_source', session('analytics.utm_source')),
                'analytics.utm_medium'   => $request->get('utm_medium', session('analytics.utm_medium')),
                'analytics.utm_campaign' => $request->get('utm_campaign', session('analytics.utm_campaign')),
                'analytics.utm_content'  => $request->get('utm_content', session('analytics.utm_content')),
            ]);
        }

        $referrer = $request->header('referer');
        $geo = app(GeoIpService::class)->lookup($request->ip());
        $adminId = auth('web')->id();

        PageVisit::create([
            'ip_address'      => $request->ip(),
            'path'            => '/' . ltrim($path, '/'),
            'referrer_url'    => $referrer,
            'referrer_source' => PageVisit::classifyReferrer($referrer),
            'utm_source'      => $request->get('utm_source'),
            'utm_medium'      => $request->get('utm_medium'),
            'utm_campaign'    => $request->get('utm_campaign'),
            'utm_content'     => $request->get('utm_content'),
            'ref_tag'         => $request->get('ref'),
            'country'         => $geo['country'],
            'region'          => $geo['region'],
            'city'            => $geo['city'],
            'org'             => $geo['org'] ?? null,
            'isp'             => $geo['isp'] ?? null,
            'is_hosting'      => $geo['hosting'] ?? false,
            'user_agent'      => mb_substr($ua, 0, 500),
            'client_id'       => auth('client')->id(),
            'admin_user_id'   => $adminId,
            'created_at'      => now(),
        ]);
    }
}
