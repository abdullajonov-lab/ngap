<?php

declare(strict_types=1);

return [
    'label' => 'Broadcast',
    'plural_label' => 'Broadcasts',
    'fields' => [
        'message' => 'Message',
        'parse_mode' => 'Parse Mode',
        'status' => 'Status',
        'total_users' => 'Total Users',
        'sent_count' => 'Sent Count',
        'failed_count' => 'Failed Count',
        'blocked_count' => 'Blocked Count',
        'started_at' => 'Started At',
        'completed_at' => 'Completed At',
        'created_at' => 'Created At',
    ],
    'actions' => [
        'create' => 'Create Broadcast',
        'cancel' => 'Cancel Broadcast',
        'view' => 'View Broadcast',
    ],
    'messages' => [
        'created' => 'Broadcast created successfully.',
        'sending' => 'Broadcast is being sent.',
        'completed' => 'Broadcast completed successfully.',
        'cancelled' => 'Broadcast has been cancelled.',
        'failed' => 'Broadcast failed.',
        'html_supported' => 'HTML formatting is supported: <b>, <i>, <u>, <s>, <a>, <code>, <pre>.',
    ],
];
