<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Middleware;

use AbdullajonovLab\NutgramAdminPanel\Contracts\TelegramUserServiceInterface;
use SergiX44\Nutgram\Nutgram;

class PersistUserMiddleware
{
    public function __construct(
        private readonly TelegramUserServiceInterface $userService,
    ) {}

    public function __invoke(Nutgram $bot, $next): void
    {
        $user = $bot->user();

        if ($user !== null && !$user->is_bot) {
            $isStartCommand = $bot->isCommand()
                && str_starts_with($bot->message()?->text ?? '', '/start');

            $this->userService->persistFromTelegram($user, $isStartCommand);
        }

        $next($bot);
    }
}
