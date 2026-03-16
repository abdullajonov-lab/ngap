<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Widgets;

use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BotStatsOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        /** @var StatisticsServiceInterface $service */
        $service = app(StatisticsServiceInterface::class);

        return [
            Stat::make(
                __('nutgram-admin-panel::statistics.fields.total_users'),
                $service->getTotalUsers(),
            )
                ->icon('heroicon-m-users')
                ->color('primary')
                ->description(__('nutgram-admin-panel::statistics.descriptions.total_users')),

            Stat::make(
                __('nutgram-admin-panel::statistics.fields.active_users'),
                $service->getActiveUsers(),
            )
                ->icon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->description(__('nutgram-admin-panel::statistics.descriptions.active_users')),

            Stat::make(
                __('nutgram-admin-panel::statistics.fields.blocked_users'),
                $service->getBlockedUsers(),
            )
                ->icon('heroicon-m-shield-exclamation')
                ->color('danger')
                ->description(__('nutgram-admin-panel::statistics.descriptions.blocked_users')),

            Stat::make(
                __('nutgram-admin-panel::statistics.fields.channels_count'),
                $service->getChannelsCount(),
            )
                ->icon('heroicon-m-signal')
                ->color('info')
                ->description(__('nutgram-admin-panel::statistics.descriptions.channels_count')),
        ];
    }
}
