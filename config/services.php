<?php

return [

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
    'url' => env('HESTIA_URL'),
    'access_key' => env('HESTIA_ACCESS_KEY'),
    'secret_key' => env('HESTIA_SECRET_KEY'),
    'package' => env('HESTIA_PACKAGE', 'default'),
    'package_map' => env('HESTIA_PACKAGE_MAP'),
    'default_domain' => env('HESTIA_DEFAULT_DOMAIN'),
    'verify_ssl' => env('HESTIA_VERIFY_SSL', true),
],

'pterodactyl' => [
    'url' => env('PTERODACTYL_URL'),
    'api_key' => env('PTERODACTYL_API_KEY'),
    'node_id' => env('PTERODACTYL_NODE_ID'),
    'location_id' => env('PTERODACTYL_LOCATION_ID', 1),
    'allocation_id' => env('PTERODACTYL_ALLOCATION_ID'),
    'docker_image' => env('PTERODACTYL_DOCKER_IMAGE', 'ghcr.io/pterodactyl/yolks:java_21'),
    'startup' => env('PTERODACTYL_STARTUP', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar {{SERVER_JARFILE}}'),
    'environment' => env('PTERODACTYL_ENVIRONMENT', '{"SERVER_JARFILE":"server.jar","MINECRAFT_VERSION":"latest"}'),
    'user_language' => env('PTERODACTYL_USER_LANGUAGE', 'en'),
    'swap' => env('PTERODACTYL_SWAP', 0),
    'io' => env('PTERODACTYL_IO', 500),
    'cpu' => env('PTERODACTYL_CPU', 0),
    'oom_disabled' => env('PTERODACTYL_OOM_DISABLED', false),
    'databases' => env('PTERODACTYL_DATABASES', 1),
    'allocations' => env('PTERODACTYL_ALLOCATIONS', 1),
    'backups' => env('PTERODACTYL_BACKUPS', 1),
    'unlimited_memory' => env('PTERODACTYL_UNLIMITED_MEMORY', 0),
    'unlimited_disk' => env('PTERODACTYL_UNLIMITED_DISK', 0),
],

];
    
