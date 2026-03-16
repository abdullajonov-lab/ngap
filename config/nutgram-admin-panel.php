<?php

declare(strict_types=1);

// config/nutgram-admin-panel.php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    | Customize database table names to avoid conflicts.
    */
    'table_names' => [
        'bot_users' => 'nutgram_bot_users',
        'channels' => 'nutgram_channels',
        'broadcasts' => 'nutgram_broadcasts',
        'admins' => 'nutgram_admins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcast Settings
    |--------------------------------------------------------------------------
    */
    'broadcast' => [
        'queue' => env('NUTGRAM_BROADCAST_QUEUE', 'default'),
        'rate_limit' => 25, // messages per second (Telegram limit is ~30, keep headroom)
        'max_tries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Join Check
    |--------------------------------------------------------------------------
    */
    'channel_check' => [
        'enabled' => true,
        'cache_ttl' => 300, // seconds (5 minutes)
    ],
];
