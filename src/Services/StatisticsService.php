<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Services;

use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Illuminate\Support\Collection;

class StatisticsService implements StatisticsServiceInterface
{
    public function getTotalUsers(): int
    {
        return BotUser::count();
    }

    public function getActiveUsers(int $hours = 24): int
    {
        return BotUser::recentlyActive($hours)->count();
    }

    public function getBlockedUsers(): int
    {
        return BotUser::blocked()->count();
    }

    public function getChannelsCount(): int
    {
        return Channel::count();
    }

    public function getUserGrowth(string $period): Collection
    {
        $trend = Trend::model(BotUser::class)
            ->between(
                start: $this->periodStart($period),
                end: now(),
            );

        return match ($period) {
            'week' => $trend->perDay()->count(),
            'month' => $trend->perDay()->count(),
            'year' => $trend->perMonth()->count(),
        };
    }

    public function getBroadcastDeliveryStats(int $limit = 10): Collection
    {
        return Broadcast::latest()
            ->take($limit)
            ->get(['id', 'message', 'sent_count', 'failed_count', 'blocked_count', 'created_at']);
    }

    public function getChannelMembershipStats(): Collection
    {
        return Channel::where('is_mandatory', true)
            ->where('is_active', true)
            ->orderByDesc('member_count')
            ->get(['title', 'username', 'member_count']);
    }

    public function getUserRetention(string $period): Collection
    {
        $start = $this->periodStart($period);

        $trend = Trend::query(
            BotUser::where('created_at', '<', $start)
        )
            ->dateColumn('last_activity_at')
            ->between(start: $start, end: now());

        return match ($period) {
            'week' => $trend->perDay()->count(),
            'month' => $trend->perDay()->count(),
            'year' => $trend->perMonth()->count(),
        };
    }

    private function periodStart(string $period): CarbonInterface
    {
        return match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
        };
    }
}
