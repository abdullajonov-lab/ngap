<?php

namespace Ngap\Controllers;

use Ngap\Models\User;
use Ngap\Models\Admin;
use Ngap\Models\Ads;
use Ngap\Models\Channel;

class StatisticsController
{
    public static function getAllUserCount(): int
    {
        return User::count();
    }

    public static function getActiveUserCount(): int
    {
        return User::active()->count();
    }

    public static function getBlockedUserCount(): int
    {
        return User::blocked()->count();
    }

    public static function getTodayUserCount(): int
    {
        return User::joinedToday()->count();
    }

    public static function getThisWeekUserCount(): int
    {
        return User::joinedThisWeek()->count();
    }

    public static function getThisMonthUserCount(): int
    {
        return User::joinedThisMonth()->count();
    }

    public static function getActiveRecentlyCount(int $days = 7): int
    {
        return User::activeRecently($days)->count();
    }

    public static function getAdminCount(): int
    {
        return Admin::count();
    }

    public static function getChannelCount(): int
    {
        return Channel::active()->count();
    }

    public static function getTotalAdsSent(): int
    {
        return Ads::completed()->count();
    }

    public static function getTotalMessagesDelivered(): int
    {
        return Ads::completed()->sum('sent_count');
    }

    public static function getAverageDeliveryRate(): float
    {
        $ads = Ads::completed()->get();
        if ($ads->isEmpty()) {
            return 0;
        }

        $totalRate = $ads->sum(fn($ad) => $ad->getSuccessRate());
        return round($totalRate / $ads->count(), 2);
    }

    public static function getStatsSummary(): array
    {
        return [
            'users' => [
                'total' => self::getAllUserCount(),
                'active' => self::getActiveUserCount(),
                'blocked' => self::getBlockedUserCount(),
                'today' => self::getTodayUserCount(),
                'this_week' => self::getThisWeekUserCount(),
                'this_month' => self::getThisMonthUserCount(),
                'active_last_7_days' => self::getActiveRecentlyCount(7),
            ],
            'admins' => [
                'total' => self::getAdminCount(),
            ],
            'channels' => [
                'total' => self::getChannelCount(),
            ],
            'ads' => [
                'total_campaigns' => self::getTotalAdsSent(),
                'total_delivered' => self::getTotalMessagesDelivered(),
                'avg_delivery_rate' => self::getAverageDeliveryRate(),
            ],
        ];
    }

    public static function getFormattedStats(): string
    {
        $stats = self::getStatsSummary();

        return sprintf(
            "📊 *Statistics*\n\n" .
            "👥 *Users*\n" .
            "├ Total: %d\n" .
            "├ Active: %d\n" .
            "├ Blocked: %d\n" .
            "├ Today: %d\n" .
            "├ This week: %d\n" .
            "├ This month: %d\n" .
            "└ Active (7 days): %d\n\n" .
            "👮 *Admins*: %d\n" .
            "📢 *Channels*: %d\n\n" .
            "📣 *Ads*\n" .
            "├ Campaigns: %d\n" .
            "├ Delivered: %d\n" .
            "└ Avg rate: %.1f%%",
            $stats['users']['total'],
            $stats['users']['active'],
            $stats['users']['blocked'],
            $stats['users']['today'],
            $stats['users']['this_week'],
            $stats['users']['this_month'],
            $stats['users']['active_last_7_days'],
            $stats['admins']['total'],
            $stats['channels']['total'],
            $stats['ads']['total_campaigns'],
            $stats['ads']['total_delivered'],
            $stats['ads']['avg_delivery_rate']
        );
    }
}
