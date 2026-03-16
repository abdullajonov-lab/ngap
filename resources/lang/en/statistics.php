<?php

declare(strict_types=1);

return [
    'label' => 'Statistics',
    'sections' => [
        'overview' => 'Overview',
        'user_growth' => 'User Growth',
        'broadcast_delivery' => 'Broadcast Delivery',
        'channel_membership' => 'Channel Membership',
        'user_retention' => 'User Retention',
    ],
    'fields' => [
        'total_users' => 'Total Users',
        'active_users' => 'Active Users',
        'blocked_users' => 'Blocked Users',
        'channels_count' => 'Channels Count',
        'new_users' => 'New Users',
        'returning_users' => 'Returning Users',
    ],
    'filters' => [
        'week' => 'Week',
        'month' => 'Month',
        'year' => 'Year',
    ],
    'descriptions' => [
        'total_users' => 'All registered bot users',
        'active_users' => 'Active in last 24 hours',
        'blocked_users' => 'Users who blocked the bot',
        'channels_count' => 'Total managed channels',
        'no_data' => 'No data available yet',
    ],
];
