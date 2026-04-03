<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'calendar' => [
        'admin_token' => env('CALENDAR_ADMIN_TOKEN', ''),
    ],

    'venmo' => [
        'handle'       => env('VENMO_HANDLE'),
        'display_name' => env('VENMO_DISPLAY_NAME'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
    ],

    'media_cdn_url'    => env('MEDIA_CDN_URL', 'https://media.kristineskates.com'),
    'media_path_prefix' => env('MEDIA_PATH_PREFIX', ''),

    'turnstile' => [
        'key'    => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'microsoft_graph' => [
        'tenant_id'     => env('MICROSOFT_GRAPH_TENANT_ID'),
        'client_id'     => env('MICROSOFT_GRAPH_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_GRAPH_CLIENT_SECRET'),
        'from_address'  => env('MICROSOFT_GRAPH_FROM_ADDRESS', 'kristine@kristineskates.com'),
    ],

];
