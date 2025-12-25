<?php

namespace Ngap\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'telegram_id' => 'integer',
        'is_main' => 'boolean',
        'can_send_ads' => 'boolean',
        'can_add_admin' => 'boolean',
        'can_del_admin' => 'boolean',
        'can_add_channel' => 'boolean',
        'can_del_channel' => 'boolean',
        'can_view_stats' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('ngap.tables.admins', 'ngap_admins');
    }

    public static function findByTelegramId(int $telegramId): ?self
    {
        return static::where('telegram_id', $telegramId)->first();
    }

    public static function isAdmin(int $telegramId): bool
    {
        return static::where('telegram_id', $telegramId)->exists();
    }

    public static function isMainAdmin(int $telegramId): bool
    {
        return static::where('telegram_id', $telegramId)
                     ->where('is_main', true)
                     ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_main) {
            return true;
        }

        return (bool) $this->getAttribute($permission);
    }

    public function canSendAds(): bool
    {
        return $this->hasPermission('can_send_ads');
    }

    public function canAddAdmin(): bool
    {
        return $this->hasPermission('can_add_admin');
    }

    public function canDeleteAdmin(): bool
    {
        return $this->hasPermission('can_del_admin');
    }

    public function canAddChannel(): bool
    {
        return $this->hasPermission('can_add_channel');
    }

    public function canDeleteChannel(): bool
    {
        return $this->hasPermission('can_del_channel');
    }

    public function canViewStats(): bool
    {
        return $this->hasPermission('can_view_stats');
    }

    public static function createWithPermissions(
        int $telegramId,
        string $name,
        array $permissions = []
    ): self {
        $defaultPermissions = [
            'can_send_ads' => false,
            'can_add_admin' => false,
            'can_del_admin' => false,
            'can_add_channel' => false,
            'can_del_channel' => false,
            'can_view_stats' => true,
        ];

        return static::create([
            'telegram_id' => $telegramId,
            'name' => $name,
            'is_main' => false,
            ...array_merge($defaultPermissions, $permissions),
        ]);
    }

    public static function createMainAdmin(int $telegramId, string $name): self
    {
        return static::updateOrCreate(
            ['telegram_id' => $telegramId],
            [
                'name' => $name,
                'is_main' => true,
                'can_send_ads' => true,
                'can_add_admin' => true,
                'can_del_admin' => true,
                'can_add_channel' => true,
                'can_del_channel' => true,
                'can_view_stats' => true,
            ]
        );
    }
}
