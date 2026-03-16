<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Pages;

use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\BotStatsOverview;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\BroadcastDeliveryChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\ChannelMembershipChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\UserGrowthChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\UserRetentionChart;
use Filament\Pages\Page;

class Statistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Bot Management';

    protected static ?int $navigationSort = -1;

    protected static string $view = 'nutgram-admin-panel::pages.statistics';

    protected static ?string $slug = '/';

    public static function getNavigationLabel(): string
    {
        return __('nutgram-admin-panel::statistics.label');
    }

    public function getTitle(): string
    {
        return __('nutgram-admin-panel::statistics.label');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BotStatsOverview::class,
            UserGrowthChart::class,
            BroadcastDeliveryChart::class,
            ChannelMembershipChart::class,
            UserRetentionChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
