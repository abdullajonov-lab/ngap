<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Models;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('nutgram-admin-panel.table_names.broadcasts', 'nutgram_broadcasts');
    }

    protected function casts(): array
    {
        return [
            'status' => BroadcastStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
