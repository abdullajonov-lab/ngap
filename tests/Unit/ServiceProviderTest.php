<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Contracts\BroadcastServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelServiceProvider;
use AbdullajonovLab\NutgramAdminPanel\Services\BroadcastService;
use AbdullajonovLab\NutgramAdminPanel\Services\ChannelService;
use AbdullajonovLab\NutgramAdminPanel\Services\StatisticsService;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        $this->assertNotNull(
            $this->app->getProvider(NutgramAdminPanelServiceProvider::class)
        );
    }

    public function test_config_is_merged(): void
    {
        $config = config('nutgram-admin-panel');

        $this->assertNotNull($config);
        $this->assertIsArray($config);
    }

    public function test_channel_service_is_bound(): void
    {
        $this->assertTrue($this->app->bound(ChannelServiceInterface::class));
        $this->assertInstanceOf(
            ChannelService::class,
            $this->app->make(ChannelServiceInterface::class)
        );
    }

    public function test_broadcast_service_is_bound(): void
    {
        $this->assertTrue($this->app->bound(BroadcastServiceInterface::class));
        $this->assertInstanceOf(
            BroadcastService::class,
            $this->app->make(BroadcastServiceInterface::class)
        );
    }

    public function test_statistics_service_is_bound(): void
    {
        $this->assertTrue($this->app->bound(StatisticsServiceInterface::class));
        $this->assertInstanceOf(
            StatisticsService::class,
            $this->app->make(StatisticsServiceInterface::class)
        );
    }
}
