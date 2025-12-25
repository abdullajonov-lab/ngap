<?php

namespace Ngap\Commands;

use Ngap\Models\User;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartCommand extends Command
{
    protected string $command = 'start';
    protected ?string $description = 'Start the bot';

    public function handle(Nutgram $bot): void
    {
        $telegramUser = $bot->user();

        if (!$telegramUser) {
            return;
        }

        // Register or update user
        $user = User::registerFromTelegram(
            telegramId: $telegramUser->id,
            username: $telegramUser->username,
            firstName: $telegramUser->first_name,
            lastName: $telegramUser->last_name,
            languageCode: $telegramUser->language_code
        );

        $name = $telegramUser->first_name ?? $telegramUser->username ?? 'User';

        $bot->sendMessage(
            text: __('ngap::main.start_message', ['name' => $name])
        );
    }
}
