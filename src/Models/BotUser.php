<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Models;

use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('nutgram-admin-panel.table_names.bot_users', 'nutgram_bot_users');
    }

    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
            'last_activity_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Blocked);
    }

    public function scopeRecentlyActive(Builder $query, int $hours = 24): Builder
    {
        return $query->where('last_activity_at', '>=', now()->subHours($hours));
    }
}
