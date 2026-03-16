<?php

declare(strict_types=1);

return [
    'label' => 'Channel',
    'plural_label' => 'Channels',
    'fields' => [
        'chat_id' => 'Chat ID',
        'title' => 'Title',
        'username' => 'Username',
        'is_mandatory' => 'Mandatory Join',
        'is_active' => 'Active',
        'member_count' => 'Member Count',
    ],
    'actions' => [
        'create' => 'Add Channel',
        'edit' => 'Edit Channel',
        'delete' => 'Delete Channel',
    ],
    'messages' => [
        'created' => 'Channel added successfully.',
        'updated' => 'Channel updated successfully.',
        'deleted' => 'Channel removed.',
        'validation_failed' => 'Could not validate channel. Ensure the bot is an admin.',
        'join_required' => 'To use this bot, please join the following channels:',
        'check_membership' => 'Check membership',
        'membership_verified' => 'Membership verified! You can now use the bot.',
        'still_not_member' => 'You have not joined all required channels yet.',
        'channel_not_found' => 'Channel not found. Please check the chat ID or username.',
        'bot_not_admin' => 'The bot is not an administrator in this channel.',
        'member_count_synced' => 'Member count synced: :count',
    ],
    'table_actions' => [
        'sync_member_count' => 'Sync',
    ],
];
