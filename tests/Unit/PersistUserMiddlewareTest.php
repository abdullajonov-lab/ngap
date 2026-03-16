<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Contracts\TelegramUserServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Middleware\PersistUserMiddleware;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use PHPUnit\Framework\TestCase;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Message\Message;
use SergiX44\Nutgram\Telegram\Types\User\User;

class PersistUserMiddlewareTest extends TestCase
{
    private TelegramUserServiceInterface $userService;
    private PersistUserMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->createMock(TelegramUserServiceInterface::class);
        $this->middleware = new PersistUserMiddleware($this->userService);
    }

    private function createMockBot(?User $user = null, ?string $messageText = null, bool $isCommand = false): Nutgram
    {
        $bot = $this->createMock(Nutgram::class);
        $bot->method('user')->willReturn($user);

        $message = null;
        if ($messageText !== null) {
            $message = $this->createMock(Message::class);
            $message->text = $messageText;
        }
        $bot->method('message')->willReturn($message);
        $bot->method('isCommand')->willReturn($isCommand);

        return $bot;
    }

    private function makeTelegramUser(int $id = 123456, bool $isBot = false): User
    {
        $user = new User();
        $user->id = $id;
        $user->first_name = 'Test';
        $user->is_bot = $isBot;

        return $user;
    }

    public function test_calls_persist_when_user_present_and_not_bot(): void
    {
        $user = $this->makeTelegramUser();
        $bot = $this->createMockBot(user: $user, messageText: 'hello');

        $this->userService->expects($this->once())
            ->method('persistFromTelegram')
            ->with($user, false)
            ->willReturn(new BotUser());

        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        ($this->middleware)($bot, $next);

        $this->assertTrue($nextCalled);
    }

    public function test_skips_persist_when_user_is_null(): void
    {
        $bot = $this->createMockBot(user: null);

        $this->userService->expects($this->never())
            ->method('persistFromTelegram');

        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        ($this->middleware)($bot, $next);

        $this->assertTrue($nextCalled);
    }

    public function test_skips_persist_when_user_is_bot(): void
    {
        $user = $this->makeTelegramUser(isBot: true);
        $bot = $this->createMockBot(user: $user);

        $this->userService->expects($this->never())
            ->method('persistFromTelegram');

        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
        };

        ($this->middleware)($bot, $next);

        $this->assertTrue($nextCalled);
    }

    public function test_passes_is_start_command_true_for_start_command(): void
    {
        $user = $this->makeTelegramUser();
        $bot = $this->createMockBot(user: $user, messageText: '/start', isCommand: true);

        $this->userService->expects($this->once())
            ->method('persistFromTelegram')
            ->with($user, true)
            ->willReturn(new BotUser());

        $nextCalled = false;
        ($this->middleware)($bot, function () use (&$nextCalled) {
            $nextCalled = true;
        });

        $this->assertTrue($nextCalled);
    }

    public function test_passes_is_start_command_true_for_start_with_payload(): void
    {
        $user = $this->makeTelegramUser();
        $bot = $this->createMockBot(user: $user, messageText: '/start ref123', isCommand: true);

        $this->userService->expects($this->once())
            ->method('persistFromTelegram')
            ->with($user, true)
            ->willReturn(new BotUser());

        ($this->middleware)($bot, function () {});
    }

    public function test_passes_is_start_command_false_for_non_start_message(): void
    {
        $user = $this->makeTelegramUser();
        $bot = $this->createMockBot(user: $user, messageText: '/help', isCommand: true);

        $this->userService->expects($this->once())
            ->method('persistFromTelegram')
            ->with($user, false)
            ->willReturn(new BotUser());

        ($this->middleware)($bot, function () {});
    }

    public function test_next_always_called_regardless_of_user_presence(): void
    {
        // Test with null user
        $botNull = $this->createMockBot(user: null);
        $nextCalledNull = false;
        ($this->middleware)($botNull, function () use (&$nextCalledNull) {
            $nextCalledNull = true;
        });
        $this->assertTrue($nextCalledNull, '$next must be called when user is null');

        // Test with bot user
        $botUser = $this->makeTelegramUser(isBot: true);
        $botWithBot = $this->createMockBot(user: $botUser);
        $nextCalledBot = false;
        ($this->middleware)($botWithBot, function () use (&$nextCalledBot) {
            $nextCalledBot = true;
        });
        $this->assertTrue($nextCalledBot, '$next must be called when user is bot');

        // Test with valid user
        $validUser = $this->makeTelegramUser();
        $botValid = $this->createMockBot(user: $validUser, messageText: 'hi');
        $this->userService->method('persistFromTelegram')->willReturn(new BotUser());
        $nextCalledValid = false;
        ($this->middleware)($botValid, function () use (&$nextCalledValid) {
            $nextCalledValid = true;
        });
        $this->assertTrue($nextCalledValid, '$next must be called when user is valid');
    }
}
