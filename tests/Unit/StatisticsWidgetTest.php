<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Filament\Pages\Statistics;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\BotStatsOverview;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\BroadcastDeliveryChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\ChannelMembershipChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\UserGrowthChart;
use AbdullajonovLab\NutgramAdminPanel\Filament\Widgets\UserRetentionChart;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use ReflectionClass;

class StatisticsWidgetTest extends TestCase
{
    public function test_statistics_page_has_landing_page_slug(): void
    {
        $reflection = new ReflectionClass(Statistics::class);
        $property = $reflection->getProperty('slug');

        $this->assertSame('/', $property->getDefaultValue());
    }

    public function test_statistics_page_has_correct_navigation_sort(): void
    {
        $reflection = new ReflectionClass(Statistics::class);
        $property = $reflection->getProperty('navigationSort');

        $this->assertSame(-1, $property->getDefaultValue());
    }

    public function test_statistics_page_header_widgets_returns_all_five_widgets(): void
    {
        $page = new Statistics();
        $reflection = new ReflectionClass($page);
        $method = $reflection->getMethod('getHeaderWidgets');
        $widgets = $method->invoke($page);

        $this->assertCount(5, $widgets);
        $this->assertContains(BotStatsOverview::class, $widgets);
        $this->assertContains(UserGrowthChart::class, $widgets);
        $this->assertContains(BroadcastDeliveryChart::class, $widgets);
        $this->assertContains(ChannelMembershipChart::class, $widgets);
        $this->assertContains(UserRetentionChart::class, $widgets);
    }

    public function test_bot_stats_overview_has_10s_polling(): void
    {
        $reflection = new ReflectionClass(BotStatsOverview::class);
        $property = $reflection->getProperty('pollingInterval');

        $this->assertSame('10s', $property->getDefaultValue());
    }

    public function test_user_growth_chart_has_30s_polling_and_line_type(): void
    {
        $reflection = new ReflectionClass(UserGrowthChart::class);
        $pollingProperty = $reflection->getProperty('pollingInterval');

        $this->assertSame('30s', $pollingProperty->getDefaultValue());

        $widget = new UserGrowthChart();
        $method = $reflection->getMethod('getType');
        $this->assertSame('line', $method->invoke($widget));
    }

    public function test_broadcast_delivery_chart_has_30s_polling_and_bar_type(): void
    {
        $reflection = new ReflectionClass(BroadcastDeliveryChart::class);
        $pollingProperty = $reflection->getProperty('pollingInterval');

        $this->assertSame('30s', $pollingProperty->getDefaultValue());

        $widget = new BroadcastDeliveryChart();
        $method = $reflection->getMethod('getType');
        $this->assertSame('bar', $method->invoke($widget));
    }

    public function test_channel_membership_chart_has_30s_polling_and_bar_type(): void
    {
        $reflection = new ReflectionClass(ChannelMembershipChart::class);
        $pollingProperty = $reflection->getProperty('pollingInterval');

        $this->assertSame('30s', $pollingProperty->getDefaultValue());

        $widget = new ChannelMembershipChart();
        $method = $reflection->getMethod('getType');
        $this->assertSame('bar', $method->invoke($widget));
    }

    public function test_user_retention_chart_has_30s_polling_and_line_type(): void
    {
        $reflection = new ReflectionClass(UserRetentionChart::class);
        $pollingProperty = $reflection->getProperty('pollingInterval');

        $this->assertSame('30s', $pollingProperty->getDefaultValue());

        $widget = new UserRetentionChart();
        $method = $reflection->getMethod('getType');
        $this->assertSame('line', $method->invoke($widget));
    }

    public function test_user_growth_chart_has_week_month_year_filters(): void
    {
        $widget = new UserGrowthChart();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getFilters');

        $filters = $method->invoke($widget);

        $this->assertArrayHasKey('week', $filters);
        $this->assertArrayHasKey('month', $filters);
        $this->assertArrayHasKey('year', $filters);
    }

    public function test_user_retention_chart_has_week_month_year_filters(): void
    {
        $widget = new UserRetentionChart();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getFilters');

        $filters = $method->invoke($widget);

        $this->assertArrayHasKey('week', $filters);
        $this->assertArrayHasKey('month', $filters);
        $this->assertArrayHasKey('year', $filters);
    }

    public function test_broadcast_delivery_chart_has_stacked_options(): void
    {
        $widget = new BroadcastDeliveryChart();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');

        $options = $method->invoke($widget);

        $this->assertTrue($options['scales']['x']['stacked']);
        $this->assertTrue($options['scales']['y']['stacked']);
    }

    public function test_channel_membership_chart_has_horizontal_axis(): void
    {
        $widget = new ChannelMembershipChart();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');

        $options = $method->invoke($widget);

        $this->assertSame('y', $options['indexAxis']);
    }
}
