<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Contracts;

use Illuminate\Support\Collection;

interface StatisticsServiceInterface
{
    // STAT-01: Stat cards
    public function getTotalUsers(): int;

    public function getActiveUsers(int $hours = 24): int;

    public function getBlockedUsers(): int;

    public function getChannelsCount(): int;

    // STAT-02: User growth
    public function getUserGrowth(string $period): Collection;

    // STAT-03: Broadcast delivery
    public function getBroadcastDeliveryStats(int $limit = 10): Collection;

    // STAT-04: Channel membership
    public function getChannelMembershipStats(): Collection;

    // STAT-05: User retention
    public function getUserRetention(string $period): Collection;
}
