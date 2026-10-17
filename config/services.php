<?php

return [
    // [SUBSTITUIR POR DADO REAL] Defina WHATSAPP_NUMBER para exibir o botão público.
    'whatsapp' => ['number' => env('WHATSAPP_NUMBER')],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
        'client_area' => [
    'url' => env('CLIENT_AREA_URL', 'http://127.0.0.1:8001'),
],
    'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

'discord' => [
    'client_id' => env('DISCORD_CLIENT_ID'),
    'client_secret' => env('DISCORD_CLIENT_SECRET'),
    'redirect' => env('DISCORD_REDIRECT_URI'),
],

'mercadopago' => [
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    'base_url' => env('MERCADOPAGO_BASE_URL', 'https://api.mercadopago.com'),
    'statement_descriptor' => env('MERCADOPAGO_STATEMENT_DESCRIPTOR', 'VORTEXHOST'),
],

'hestia' => [
    'driver' => env('HESTIA_DRIVER', 'api'),
    'url' => env('HESTIA_URL'),
    'access_key' => env('HESTIA_ACCESS_KEY'),
    'secret_key' => env('HESTIA_SECRET_KEY'),
    'user' => env('HESTIA_USER'),
    'password' => env('HESTIA_PASSWORD'),
    'bin_path' => env('HESTIA_BIN_PATH', '/usr/local/hestia/bin'),
    'local_use_sudo' => env('HESTIA_LOCAL_USE_SUDO', false),
    'queue_when_exec_disabled' => env('HESTIA_QUEUE_WHEN_EXEC_DISABLED', true),
    'queue_path' => env('HESTIA_QUEUE_PATH', storage_path('app/hestia-queue')),
    'autologin' => env('HESTIA_AUTOLOGIN', true),
    'package' => env('HESTIA_PACKAGE', 'default'),
    'package_map' => env('HESTIA_PACKAGE_MAP'),
    'package_from_product' => env('HESTIA_PACKAGE_FROM_PRODUCT', true),
    'default_domain' => env('HESTIA_DEFAULT_DOMAIN'),
    'create_domain' => env('HESTIA_CREATE_DOMAIN', false),
    'verify_ssl' => env('HESTIA_VERIFY_SSL', true),
],

'pterodactyl' => [
    'url' => env('PTERODACTYL_URL'),
    'api_key' => env('PTERODACTYL_API_KEY'),
    'node_id' => env('PTERODACTYL_NODE_ID'),
    'location_id' => env('PTERODACTYL_LOCATION_ID', 1),
    'allocation_id' => env('PTERODACTYL_ALLOCATION_ID'),
    'user_language' => env('PTERODACTYL_USER_LANGUAGE', 'en'),
    'swap' => env('PTERODACTYL_SWAP', 0),
    'io' => env('PTERODACTYL_IO', 500),
    'cpu' => env('PTERODACTYL_CPU', 0),
    'oom_disabled' => env('PTERODACTYL_OOM_DISABLED', false),
    'databases' => env('PTERODACTYL_DATABASES', 1),
    'database_host_id' => env('PTERODACTYL_DATABASE_HOST_ID', 2),
    'allocations' => env('PTERODACTYL_ALLOCATIONS', 1),
    'backups' => env('PTERODACTYL_BACKUPS', 1),
    'unlimited_memory' => env('PTERODACTYL_UNLIMITED_MEMORY', 0),
    'unlimited_disk' => env('PTERODACTYL_UNLIMITED_DISK', 0),
],

];
    
