<?php

namespace Ngap\Middleware;

use Ngap\Models\User;
use SergiX44\Nutgram\Nutgram;

class RegisterUser
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $telegramUser = $bot->user();

        if (!$telegramUser) {
            $next($bot);
            return;
        }

        $user = User::registerFromTelegram(
            telegramId: $telegramUser->id,
            username: $telegramUser->username,
            firstName: $telegramUser->first_name,
            lastName: $telegramUser->last_name,
            languageCode: $telegramUser->language_code
        );

        // Update last activity
        $user->updateActivity();

        $next($bot);
    }
}
