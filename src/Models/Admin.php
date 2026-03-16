<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Models;

use AbdullajonovLab\NutgramAdminPanel\Enums\AdminRole;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'role' => AdminRole::class,
    ];

    public function getTable(): string
    {
        return config('nutgram-admin-panel.table_names.admins', 'nutgram_admins');
    }
}
