<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel;

use AbdullajonovLab\NutgramAdminPanel\Filament\Pages\Statistics;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BotUserResource;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class NutgramAdminPanelPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'nutgram-admin-panel';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            AdminResource::class,
            BotUserResource::class,
            BroadcastResource::class,
            ChannelResource::class,
        ])->pages([
            Statistics::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
