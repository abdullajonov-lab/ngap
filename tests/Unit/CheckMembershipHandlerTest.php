<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Handlers\CheckMembershipHandler;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Inline\CallbackQuery;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

class CheckMembershipHandlerTest extends TestCase
{
    private ChannelServiceInterface&MockObject $channelService;

    private CheckMembershipHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channelService = $this->createMock(ChannelServiceInterface::class);
        $this->handler = new CheckMembershipHandler($this->channelService);
    }

    private function createMockBot(int $userId = 123456, int $chatId = 123456, int $messageId = 42): Nutgram&MockObject
    {
        $bot = $this->createMock(Nutgram::class);
        $bot->method('userId')->willReturn($userId);
        $bot->method('chatId')->willReturn($chatId);

        $message = new Message();
        $message->message_id = $messageId;

        $callbackQuery = new CallbackQuery();
        $callbackQuery->message = $message;

        $bot->method('callbackQuery')->willReturn($callbackQuery);

        return $bot;
    }

    public function test_grants_access_when_all_channels_joined(): void
    {
        $bot = $this->createMockBot();

        $this->channelService->expects($this->once())
            ->method('getMissingChannelsFresh')
            ->with($bot, 123456)
            ->willReturn([]);

        $bot->expects($this->once())
            ->method('answerCallbackQuery')
            ->with($this->callback(function () {
                return true;
            }));

        $bot->expects($this->once())
            ->method('deleteMessage')
            ->with(123456, 42);

        ($this->handler)($bot);
    }

    public function test_shows_alert_when_still_not_member(): void
    {
        $bot = $this->createMockBot();

        $channel = new Channel();
        $channel->chat_id = -1001234567890;
        $channel->title = 'Test Channel';
        $missingChannels = [$channel];

        $this->channelService->expects($this->once())
            ->method('getMissingChannelsFresh')
            ->with($bot, 123456)
            ->willReturn($missingChannels);

        $bot->expects($this->once())
            ->method('answerCallbackQuery');

        $this->channelService->expects($this->once())
            ->method('sendJoinPrompt')
            ->with($bot, $missingChannels);

        ($this->handler)($bot);
    }

    public function test_always_answers_callback_query(): void
    {
        // Test success path answers callback
        $bot1 = $this->createMockBot();
        $this->channelService->method('getMissingChannelsFresh')->willReturn([]);
        $bot1->expects($this->once())->method('answerCallbackQuery');
        ($this->handler)($bot1);

        // Test failure path answers callback (covered by test_shows_alert_when_still_not_member)
        // Both paths call answerCallbackQuery -- verified above
    }
}
