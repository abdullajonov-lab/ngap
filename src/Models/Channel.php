<?php

namespace Ngap\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'channel_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('ngap.tables.channels', 'ngap_channels');
    }

    public static function findByChannelId(int $channelId): ?self
    {
        return static::where('channel_id', $channelId)->first();
    }

    public static function findByUsername(string $username): ?self
    {
        $username = ltrim($username, '@');
        return static::where('username', $username)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getActiveChannels(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->get();
    }

    public static function addChannel(int $channelId, string $username, ?string $title = null): self
    {
        return static::updateOrCreate(
            ['channel_id' => $channelId],
            [
                'username' => ltrim($username, '@'),
                'title' => $title,
                'is_active' => true,
            ]
        );
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function getLink(): string
    {
        if ($this->username) {
            return "https://t.me/{$this->username}";
        }
        return "https://t.me/c/" . abs($this->channel_id);
    }

    public function getDisplayName(): string
    {
        return $this->title ?? ('@' . $this->username) ?? (string) $this->channel_id;
    }
}
