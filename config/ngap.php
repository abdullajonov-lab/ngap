<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    */
    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Configuration
    |--------------------------------------------------------------------------
    */
    'admin' => [
        // Main admin Telegram ID - has all permissions
        'main_admin_id' => env('NGAP_MAIN_ADMIN_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    */
    'localization' => [
        'default_locale' => 'en',
        'available_locales' => ['en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Checking
    |--------------------------------------------------------------------------
    */
    'subscription' => [
        'enabled' => true,
        // Commands that bypass subscription check
        'bypass_commands' => ['start', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Advertisement Broadcasting
    |--------------------------------------------------------------------------
    */
    'ads' => [
        // Messages per second (Telegram limit is ~30/sec)
        'batch_size' => 25,
        // Delay between batches in milliseconds
        'batch_delay' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'users' => 'ngap_users',
        'admins' => 'ngap_admins',
        'channels' => 'ngap_channels',
        'ads' => 'ngap_ads',
    ],
];
