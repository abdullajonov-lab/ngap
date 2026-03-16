<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Middleware\ChannelJoinMiddleware;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SergiX44\Nutgram\Nutgram;

class ChannelJoinMiddlewareTest extends TestCase
{
    private ChannelServiceInterface&MockObject $channelService;

    private ChannelJoinMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channelService = $this->createMock(ChannelServiceInterface::class);
        $this->middleware = new ChannelJoinMiddleware($this->channelService);
    }

    private function createMockBot(?int $userId = 123456): Nutgram&MockObject
    {
        $bot = $this->createMock(Nutgram::class);
        $bot->method('userId')->willReturn($userId);

        return $bot;
    }

    public function test_skips_when_channel_check_disabled(): void
    {
        config()->set('nutgram-admin-panel.channel_check.enabled', false);

        $bot = $this->createMockBot();
        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        $this->channelService->expects($this->never())
            ->method('getMissingChannels');

        ($this->middleware)($bot, $next);

        $this->assertTrue($nextCalled);
    }

    public function test_skips_when_user_id_null(): void
    {
        config()->set('nutgram-admin-panel.channel_check.enabled', true);

        $bot = $this->createMockBot(userId: null);
        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        $this->channelService->expects($this->never())
            ->method('getMissingChannels');

        ($this->middleware)($bot, $next);

        $this->assertTrue($nextCalled);
    }

    public function test_calls_next_when_user_is_member_of_all(): void
    {
        config()->set('nutgram-admin-panel.channel_check.enabled', true);

        $bot = $this->createMockBot();
        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        $this->channelService->expects($this->once())
            ->method('getMissingChannels')
            ->with($bot, 123456)
            ->willReturn([]);

        ($this->middleware)($bot, $next);

        $this->assertTrue($nextCalled);
    }

    public function test_blocks_and_shows_prompt_when_not_member(): void
    {
        config()->set('nutgram-admin-panel.channel_check.enabled', true);

        $bot = $this->createMockBot();
        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        $channel = new Channel();
        $channel->chat_id = -1001234567890;
        $channel->title = 'Test Channel';
        $missingChannels = [$channel];

        $this->channelService->expects($this->once())
            ->method('getMissingChannels')
            ->with($bot, 123456)
            ->willReturn($missingChannels);

        $this->channelService->expects($this->once())
            ->method('sendJoinPrompt')
            ->with($bot, $missingChannels);

        ($this->middleware)($bot, $next);

        $this->assertFalse($nextCalled);
    }
}
