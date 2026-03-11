<?php

namespace App\Providers;

use App\Mail\MicrosoftGraphTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Mail::extend('microsoft-graph', function (array $config) {
            return new MicrosoftGraphTransport(
                $config['tenant_id']     ?? config('services.microsoft_graph.tenant_id'),
                $config['client_id']     ?? config('services.microsoft_graph.client_id'),
                $config['client_secret'] ?? config('services.microsoft_graph.client_secret'),
                $config['from_address']  ?? config('mail.from.address'),
            );
        });
    }
}
