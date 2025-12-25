<?php

namespace Ngap\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'telegram_id' => 'integer',
        'is_blocked' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('ngap.tables.users', 'ngap_users');
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ads::class, 'admin_telegram_id', 'telegram_id');
    }

    public static function findByTelegramId(int $telegramId): ?self
    {
        return static::where('telegram_id', $telegramId)->first();
    }

    public static function registerFromTelegram(
        int $telegramId,
        ?string $username,
        ?string $firstName,
        ?string $lastName,
        ?string $languageCode = null
    ): self {
        return static::updateOrCreate(
            ['telegram_id' => $telegramId],
            [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'language_code' => $languageCode ?? config('ngap.localization.default_locale', 'en'),
                'last_active_at' => now(),
            ]
        );
    }

    public function updateActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    public function block(): void
    {
        $this->update(['is_blocked' => true]);
    }

    public function unblock(): void
    {
        $this->update(['is_blocked' => false]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    public function scopeJoinedToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeJoinedThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeJoinedThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeActiveRecently($query, int $days = 7)
    {
        return $query->where('last_active_at', '>=', now()->subDays($days));
    }
}
