<?php

return [

    'default' => env('MAIL_MAILER', 'microsoft-graph'),

    'mailers' => [

        'microsoft-graph' => [
            'transport'     => 'microsoft-graph',
            'tenant_id'     => env('MICROSOFT_GRAPH_TENANT_ID'),
            'client_id'     => env('MICROSOFT_GRAPH_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_GRAPH_CLIENT_SECRET'),
            'from_address'  => env('MICROSOFT_GRAPH_FROM_ADDRESS', 'kristine@kristineskates.com'),
        ],

        'smtp' => [
            'transport'    => 'smtp',
            'url'          => env('MAIL_URL'),
            'host'         => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port'         => env('MAIL_PORT', 587),
            'encryption'   => env('MAIL_ENCRYPTION', 'tls'),
            'username'     => env('MAIL_USERNAME'),
            'password'     => env('MAIL_PASSWORD'),
            'timeout'      => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path'      => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers'   => ['microsoft-graph', 'log'],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'kristine@kristineskates.com'),
        'name'    => env('MAIL_FROM_NAME', 'Coach Kristine'),
    ],

    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
