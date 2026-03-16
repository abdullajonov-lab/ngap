<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Services\TelegramUserService;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SergiX44\Nutgram\Telegram\Types\User\User;

class TelegramUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private TelegramUserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TelegramUserService();
    }

    private function makeTelegramUser(
        int $id = 123456,
        string $firstName = 'John',
        ?string $lastName = 'Doe',
        ?string $username = 'johndoe',
        ?string $languageCode = 'en',
        bool $isBot = false,
    ): User {
        $user = new User();
        $user->id = $id;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->username = $username;
        $user->language_code = $languageCode;
        $user->is_bot = $isBot;

        return $user;
    }

    public function test_persist_creates_new_bot_user_when_telegram_id_does_not_exist(): void
    {
        $telegramUser = $this->makeTelegramUser();

        $botUser = $this->service->persistFromTelegram($telegramUser);

        $this->assertDatabaseHas('nutgram_bot_users', [
            'telegram_id' => 123456,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'language_code' => 'en',
        ]);
        $this->assertInstanceOf(BotUser::class, $botUser);
        $this->assertEquals(UserStatus::Active, $botUser->status);
    }

    public function test_persist_updates_existing_bot_user_fields(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'Old',
            'last_name' => 'Name',
            'username' => 'oldname',
            'language_code' => 'ru',
            'status' => UserStatus::Active,
        ]);

        $telegramUser = $this->makeTelegramUser(
            firstName: 'New',
            lastName: 'Name2',
            username: 'newname',
            languageCode: 'en',
        );

        $botUser = $this->service->persistFromTelegram($telegramUser);

        $this->assertEquals('New', $botUser->first_name);
        $this->assertEquals('Name2', $botUser->last_name);
        $this->assertEquals('newname', $botUser->username);
        $this->assertEquals('en', $botUser->language_code);
        $this->assertCount(1, BotUser::all());
    }

    public function test_persist_always_updates_last_activity_at(): void
    {
        $telegramUser = $this->makeTelegramUser();

        $botUser = $this->service->persistFromTelegram($telegramUser);

        $this->assertNotNull($botUser->last_activity_at);
        $this->assertEqualsWithDelta(now()->timestamp, $botUser->last_activity_at->timestamp, 2);
    }

    public function test_persist_with_start_command_reactivates_blocked_user(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'John',
            'status' => UserStatus::Blocked,
        ]);

        $telegramUser = $this->makeTelegramUser();
        $botUser = $this->service->persistFromTelegram($telegramUser, isStartCommand: true);

        $this->assertEquals(UserStatus::Active, $botUser->status);
    }

    public function test_persist_with_start_command_reactivates_deactivated_user(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'John',
            'status' => UserStatus::Deactivated,
        ]);

        $telegramUser = $this->makeTelegramUser();
        $botUser = $this->service->persistFromTelegram($telegramUser, isStartCommand: true);

        $this->assertEquals(UserStatus::Active, $botUser->status);
    }

    public function test_persist_without_start_command_does_not_change_blocked_status(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'John',
            'status' => UserStatus::Blocked,
        ]);

        $telegramUser = $this->makeTelegramUser();
        $botUser = $this->service->persistFromTelegram($telegramUser, isStartCommand: false);

        $this->assertEquals(UserStatus::Blocked, $botUser->status);
    }

    public function test_persist_without_start_command_does_not_change_deactivated_status(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'John',
            'status' => UserStatus::Deactivated,
        ]);

        $telegramUser = $this->makeTelegramUser();
        $botUser = $this->service->persistFromTelegram($telegramUser, isStartCommand: false);

        $this->assertEquals(UserStatus::Deactivated, $botUser->status);
    }

    public function test_mark_blocked_sets_user_status_to_blocked(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'John',
            'status' => UserStatus::Active,
        ]);

        $this->service->markBlocked(123456);

        $this->assertDatabaseHas('nutgram_bot_users', [
            'telegram_id' => 123456,
            'status' => UserStatus::Blocked->value,
        ]);
    }

    public function test_mark_deactivated_sets_user_status_to_deactivated(): void
    {
        BotUser::create([
            'telegram_id' => 123456,
            'first_name' => 'John',
            'status' => UserStatus::Active,
        ]);

        $this->service->markDeactivated(123456);

        $this->assertDatabaseHas('nutgram_bot_users', [
            'telegram_id' => 123456,
            'status' => UserStatus::Deactivated->value,
        ]);
    }

    public function test_bot_user_model_has_query_scopes(): void
    {
        BotUser::create([
            'telegram_id' => 1,
            'first_name' => 'Active',
            'status' => UserStatus::Active,
            'last_activity_at' => now(),
        ]);
        BotUser::create([
            'telegram_id' => 2,
            'first_name' => 'Blocked',
            'status' => UserStatus::Blocked,
            'last_activity_at' => now()->subHours(48),
        ]);
        BotUser::create([
            'telegram_id' => 3,
            'first_name' => 'Deactivated',
            'status' => UserStatus::Deactivated,
            'last_activity_at' => now()->subHours(2),
        ]);

        $this->assertCount(1, BotUser::active()->get());
        $this->assertCount(1, BotUser::blocked()->get());
        $this->assertCount(2, BotUser::recentlyActive(hours: 24)->get());
    }
}
