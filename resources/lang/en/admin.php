<?php

declare(strict_types=1);

return [
    'label' => 'Admin',
    'plural_label' => 'Admins',
    'fields' => [
        'telegram_id' => 'Telegram ID',
        'name' => 'Name',
        'role' => 'Role',
        'roles' => [
            'admin' => 'Admin',
            'super_admin' => 'Super Admin',
        ],
    ],
    'actions' => [
        'create' => 'Add Admin',
        'edit' => 'Edit Admin',
        'delete' => 'Remove Admin',
    ],
    'messages' => [
        'created' => 'Admin added successfully.',
        'updated' => 'Admin updated successfully.',
        'deleted' => 'Admin removed.',
        'last_admin_warning' => 'Cannot remove the last admin.',
    ],
];
