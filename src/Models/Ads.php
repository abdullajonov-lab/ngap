<?php

namespace Ngap\Models;

use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'admin_telegram_id' => 'integer',
        'message_id' => 'integer',
        'from_chat_id' => 'integer',
        'total_users' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('ngap.tables.ads', 'ngap_ads');
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENDING = 'sending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function create(array $attributes = []): self
    {
        $defaults = [
            'status' => self::STATUS_PENDING,
            'total_users' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ];

        return parent::query()->create(array_merge($defaults, $attributes));
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_telegram_id', 'telegram_id');
    }

    public function markAsSending(int $totalUsers): void
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'total_users' => $totalUsers,
        ]);
    }

    public function incrementSent(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_count');
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getSuccessRate(): float
    {
        if ($this->total_users === 0) {
            return 0;
        }
        return round(($this->sent_count / $this->total_users) * 100, 2);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByAdmin($query, int $adminTelegramId)
    {
        return $query->where('admin_telegram_id', $adminTelegramId);
    }
}
