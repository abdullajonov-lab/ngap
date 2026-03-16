<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Jobs\SendBroadcastMessage;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class SendBroadcastMessageTest extends TestCase
{
    use RefreshDatabase;

    private function createBroadcast(array $overrides = []): Broadcast
    {
        return Broadcast::create(array_merge([
            'message' => 'Hello <b>World</b>',
            'status' => BroadcastStatus::Sending,
            'total_users' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
            'blocked_count' => 0,
        ], $overrides));
    }

    private function createBotUser(int $telegramId = 123456): BotUser
    {
        return BotUser::create([
            'telegram_id' => $telegramId,
            'first_name' => 'Test',
            'status' => UserStatus::Active,
        ]);
    }

    public function test_sends_message_and_increments_sent_count(): void
    {
        $broadcast = $this->createBroadcast();
        $this->createBotUser(123456);

        $bot = $this->createMock(Nutgram::class);
        $bot->expects($this->once())
            ->method('sendMessage')
            ->with(
                $this->equalTo('Hello <b>World</b>'),
                $this->equalTo(123456),
                $this->anything(),
                $this->equalTo(ParseMode::HTML),
            );

        $this->app->instance(Nutgram::class, $bot);

        $job = new SendBroadcastMessage($broadcast->id, 123456);
        $job->handle($bot);

        $broadcast->refresh();
        $this->assertEquals(1, $broadcast->sent_count);
    }

    public function test_skips_cancelled_broadcast(): void
    {
        $broadcast = $this->createBroadcast(['status' => BroadcastStatus::Cancelled]);

        $bot = $this->createMock(Nutgram::class);
        $bot->expects($this->never())->method('sendMessage');

        $this->app->instance(Nutgram::class, $bot);

        $job = new SendBroadcastMessage($broadcast->id, 123456);
        $job->handle($bot);

        $broadcast->refresh();
        $this->assertEquals(0, $broadcast->sent_count);
    }

    public function test_permanent_error_increments_blocked_count(): void
    {
        $broadcast = $this->createBroadcast();
        $botUser = $this->createBotUser(123456);

        $bot = $this->createMock(Nutgram::class);
        $bot->method('sendMessage')
            ->willThrowException(new TelegramException('Forbidden: bot was blocked by the user', 403));

        $this->app->instance(Nutgram::class, $bot);

        $job = new SendBroadcastMessage($broadcast->id, 123456);

        try {
            $job->handle($bot);
        } catch (\Throwable) {
            // The job calls $this->fail() which may throw in test context
        }

        $broadcast->refresh();
        $this->assertEquals(1, $broadcast->blocked_count);

        $botUser->refresh();
        $this->assertEquals(UserStatus::Blocked, $botUser->status);
    }

    public function test_transient_429_releases_job(): void
    {
        $broadcast = $this->createBroadcast();
        $this->createBotUser(123456);

        $bot = $this->createMock(Nutgram::class);
        $bot->method('sendMessage')
            ->willThrowException(new TelegramException('Too Many Requests', 429, null, ['retry_after' => 10]));

        $this->app->instance(Nutgram::class, $bot);

        $job = new SendBroadcastMessage($broadcast->id, 123456);

        // We need to test that release is called, not fail
        // Use a partial mock on the job
        $jobMock = $this->getMockBuilder(SendBroadcastMessage::class)
            ->setConstructorArgs([$broadcast->id, 123456])
            ->onlyMethods(['release', 'fail'])
            ->getMock();

        $jobMock->expects($this->once())
            ->method('release')
            ->with(10);

        $jobMock->expects($this->never())
            ->method('fail');

        $jobMock->handle($bot);

        // No counter should have been incremented
        $broadcast->refresh();
        $this->assertEquals(0, $broadcast->sent_count);
        $this->assertEquals(0, $broadcast->blocked_count);
        $this->assertEquals(0, $broadcast->failed_count);
    }

    public function test_transient_500_releases_job(): void
    {
        $broadcast = $this->createBroadcast();
        $this->createBotUser(123456);

        $bot = $this->createMock(Nutgram::class);
        $bot->method('sendMessage')
            ->willThrowException(new TelegramException('Internal Server Error', 500));

        $this->app->instance(Nutgram::class, $bot);

        $jobMock = $this->getMockBuilder(SendBroadcastMessage::class)
            ->setConstructorArgs([$broadcast->id, 123456])
            ->onlyMethods(['release', 'fail'])
            ->getMock();

        $jobMock->expects($this->once())
            ->method('release')
            ->with(5);

        $jobMock->expects($this->never())
            ->method('fail');

        $jobMock->handle($bot);

        // No counter should have been incremented
        $broadcast->refresh();
        $this->assertEquals(0, $broadcast->sent_count);
        $this->assertEquals(0, $broadcast->blocked_count);
    }

    public function test_completion_detection(): void
    {
        $broadcast = $this->createBroadcast([
            'total_users' => 1,
            'sent_count' => 0,
        ]);
        $this->createBotUser(123456);

        $bot = $this->createMock(Nutgram::class);
        $this->app->instance(Nutgram::class, $bot);

        $job = new SendBroadcastMessage($broadcast->id, 123456);
        $job->handle($bot);

        $broadcast->refresh();
        $this->assertEquals(BroadcastStatus::Completed, $broadcast->status);
        $this->assertNotNull($broadcast->completed_at);
    }
}
