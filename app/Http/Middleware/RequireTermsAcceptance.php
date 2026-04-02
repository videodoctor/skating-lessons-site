<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTermsAcceptance
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = auth('client')->user();

        if (!$client || session('impersonating_admin_id')) {
            return $next($request);
        }

        // Check if client needs to complete consent/terms
        $needsConsent = !$client->email_consent_at || !$client->hasSignedCurrentWaiver();

        // Also catch admin-created accounts that haven't set password
        $needsSetup = $client->must_accept_terms && !$client->terms_accepted_at;

        if ($needsConsent || $needsSetup) {
            $allowed = ['client.accept-terms', 'client.accept-terms.submit', 'client.logout', 'terms', 'privacy', 'waiver.show', 'waiver.sign'];
            if (!$request->routeIs(...$allowed)) {
                return redirect()->route('client.accept-terms');
            }
        }

        return $next($request);
    }
}
