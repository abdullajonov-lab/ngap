<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel;

use AbdullajonovLab\NutgramAdminPanel\Contracts\BroadcastServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Contracts\TelegramUserServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Filament\Pages\Statistics;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\BotStatsOverview;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\BroadcastDeliveryChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\ChannelMembershipChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\UserGrowthChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\UserRetentionChart;
use AbdullajonovLab\NutgramAdminPanel\Services\BroadcastService;
use AbdullajonovLab\NutgramAdminPanel\Services\ChannelService;
use AbdullajonovLab\NutgramAdminPanel\Services\StatisticsService;
use AbdullajonovLab\NutgramAdminPanel\Services\TelegramUserService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Livewire;
use SergiX44\Nutgram\Nutgram;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NutgramAdminPanelServiceProvider extends PackageServiceProvider
{
    public static string $name = 'nutgram-admin-panel';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigrations([
                'create_nutgram_bot_users_table',
                'create_nutgram_channels_table',
                'create_nutgram_broadcasts_table',
                'create_nutgram_admins_table',
            ])
            ->runsMigrations()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->app->bind(
            ChannelServiceInterface::class,
            ChannelService::class
        );
        $this->app->bind(
            BroadcastServiceInterface::class,
            BroadcastService::class
        );
        $this->app->bind(
            StatisticsServiceInterface::class,
            StatisticsService::class
        );
        $this->app->bind(
            TelegramUserServiceInterface::class,
            TelegramUserService::class
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function packageBooted(): void
    {
        Livewire::component('abdullajonov-lab.nutgram-admin-panel.filament.pages.statistics', Statistics::class);
        Livewire::component('abdullajonov-lab.nutgram-admin-panel.filament.widgets.bot-stats-overview', BotStatsOverview::class);
        Livewire::component('abdullajonov-lab.nutgram-admin-panel.filament.widgets.user-growth-chart', UserGrowthChart::class);
        Livewire::component('abdullajonov-lab.nutgram-admin-panel.filament.widgets.broadcast-delivery-chart', BroadcastDeliveryChart::class);
        Livewire::component('abdullajonov-lab.nutgram-admin-panel.filament.widgets.channel-membership-chart', ChannelMembershipChart::class);
        Livewire::component('abdullajonov-lab.nutgram-admin-panel.filament.widgets.user-retention-chart', UserRetentionChart::class);

        if ($this->app->bound(Nutgram::class)) {
            $bot = $this->app->make(Nutgram::class);

            $routeFile = base_path('routes/telegram.php');
            $packageRouteFile = __DIR__ . '/../routes/telegram.php';

            if (file_exists($routeFile)) {
                require $routeFile;
            } else {
                require $packageRouteFile;
            }
        }

        $this->publishes([
            __DIR__ . '/../routes/telegram.php' => base_path('routes/telegram.php'),
        ], 'nutgram-admin-panel-routes');
    }
}
