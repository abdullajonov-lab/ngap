<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticsServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(StatisticsServiceInterface::class);
    }

    // STAT-01: Stat card counts

    public function test_get_total_users_returns_count_of_all_bot_users(): void
    {
        BotUser::create(['telegram_id' => 1, 'first_name' => 'Alice', 'status' => UserStatus::Active]);
        BotUser::create(['telegram_id' => 2, 'first_name' => 'Bob', 'status' => UserStatus::Blocked]);
        BotUser::create(['telegram_id' => 3, 'first_name' => 'Carol', 'status' => UserStatus::Deactivated]);

        $this->assertEquals(3, $this->service->getTotalUsers());
    }

    public function test_get_active_users_returns_count_of_recently_active_users(): void
    {
        BotUser::create([
            'telegram_id' => 1,
            'first_name' => 'Active',
            'status' => UserStatus::Active,
            'last_activity_at' => now()->subHours(12),
        ]);
        BotUser::create([
            'telegram_id' => 2,
            'first_name' => 'Inactive',
            'status' => UserStatus::Active,
            'last_activity_at' => now()->subHours(48),
        ]);
        BotUser::create([
            'telegram_id' => 3,
            'first_name' => 'NoActivity',
            'status' => UserStatus::Active,
            'last_activity_at' => null,
        ]);

        $this->assertEquals(1, $this->service->getActiveUsers(24));
    }

    public function test_get_blocked_users_returns_count_of_blocked_users(): void
    {
        BotUser::create(['telegram_id' => 1, 'first_name' => 'Active', 'status' => UserStatus::Active]);
        BotUser::create(['telegram_id' => 2, 'first_name' => 'Blocked1', 'status' => UserStatus::Blocked]);
        BotUser::create(['telegram_id' => 3, 'first_name' => 'Blocked2', 'status' => UserStatus::Blocked]);

        $this->assertEquals(2, $this->service->getBlockedUsers());
    }

    public function test_get_channels_count_returns_count_of_all_channels(): void
    {
        Channel::create(['chat_id' => '-100001', 'title' => 'Channel A', 'is_mandatory' => true, 'is_active' => true, 'member_count' => 100]);
        Channel::create(['chat_id' => '-100002', 'title' => 'Channel B', 'is_mandatory' => false, 'is_active' => true, 'member_count' => 50]);

        $this->assertEquals(2, $this->service->getChannelsCount());
    }

    // STAT-02: User growth

    public function test_get_user_growth_week_returns_daily_data_for_last_seven_days(): void
    {
        BotUser::create([
            'telegram_id' => 1,
            'first_name' => 'Recent',
            'status' => UserStatus::Active,
            'created_at' => now()->subDays(2),
        ]);

        $result = $this->service->getUserGrowth('week');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThanOrEqual(7, $result->count());
        $this->assertNotNull($result->first()->date);
        $this->assertNotNull($result->first()->aggregate);
    }

    public function test_get_user_growth_month_returns_daily_data_for_last_thirty_days(): void
    {
        BotUser::create([
            'telegram_id' => 1,
            'first_name' => 'Recent',
            'status' => UserStatus::Active,
            'created_at' => now()->subDays(10),
        ]);

        $result = $this->service->getUserGrowth('month');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThanOrEqual(28, $result->count());
    }

    public function test_get_user_growth_year_returns_monthly_data_for_last_twelve_months(): void
    {
        BotUser::create([
            'telegram_id' => 1,
            'first_name' => 'OldUser',
            'status' => UserStatus::Active,
            'created_at' => now()->subMonths(3),
        ]);

        $result = $this->service->getUserGrowth('year');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThanOrEqual(12, $result->count());
    }

    // STAT-03: Broadcast delivery

    public function test_get_broadcast_delivery_stats_returns_recent_broadcasts(): void
    {
        Broadcast::create([
            'message' => 'First broadcast',
            'status' => BroadcastStatus::Completed,
            'total_users' => 100,
            'sent_count' => 90,
            'failed_count' => 5,
            'blocked_count' => 5,
        ]);
        Broadcast::create([
            'message' => 'Second broadcast',
            'status' => BroadcastStatus::Completed,
            'total_users' => 200,
            'sent_count' => 180,
            'failed_count' => 10,
            'blocked_count' => 10,
        ]);

        $result = $this->service->getBroadcastDeliveryStats(10);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals(270, $result->sum('sent_count'));
        $this->assertEquals(15, $result->sum('failed_count'));
        $this->assertEquals(15, $result->sum('blocked_count'));
    }

    public function test_get_broadcast_delivery_stats_returns_empty_when_no_broadcasts(): void
    {
        $result = $this->service->getBroadcastDeliveryStats(10);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    // STAT-04: Channel membership

    public function test_get_channel_membership_stats_returns_mandatory_active_channels(): void
    {
        Channel::create(['chat_id' => '-100001', 'title' => 'Mandatory Active', 'username' => 'mandatory_chan', 'is_mandatory' => true, 'is_active' => true, 'member_count' => 500]);
        Channel::create(['chat_id' => '-100002', 'title' => 'Optional', 'is_mandatory' => false, 'is_active' => true, 'member_count' => 300]);
        Channel::create(['chat_id' => '-100003', 'title' => 'Mandatory Inactive', 'is_mandatory' => true, 'is_active' => false, 'member_count' => 200]);

        $result = $this->service->getChannelMembershipStats();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals('Mandatory Active', $result->first()->title);
        $this->assertEquals(500, $result->first()->member_count);
    }

    public function test_get_channel_membership_stats_returns_empty_when_no_mandatory_channels(): void
    {
        Channel::create(['chat_id' => '-100001', 'title' => 'Optional', 'is_mandatory' => false, 'is_active' => true, 'member_count' => 100]);

        $result = $this->service->getChannelMembershipStats();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    // STAT-05: User retention

    public function test_get_user_retention_month_returns_returning_user_data(): void
    {
        // User created before the period, active within it
        BotUser::create([
            'telegram_id' => 1,
            'first_name' => 'Returning',
            'status' => UserStatus::Active,
            'created_at' => now()->subMonths(3),
            'last_activity_at' => now()->subDays(5),
        ]);
        // User created within the period (not a returning user)
        BotUser::create([
            'telegram_id' => 2,
            'first_name' => 'New',
            'status' => UserStatus::Active,
            'created_at' => now()->subDays(5),
            'last_activity_at' => now()->subDays(3),
        ]);

        $result = $this->service->getUserRetention('month');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThanOrEqual(28, $result->count());
        // At least one data point should have aggregate > 0 for the returning user
        $this->assertGreaterThan(0, $result->sum('aggregate'));
    }
}
