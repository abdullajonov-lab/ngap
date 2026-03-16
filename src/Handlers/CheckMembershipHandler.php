<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Handlers;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use SergiX44\Nutgram\Nutgram;

class CheckMembershipHandler
{
    public function __construct(
        private readonly ChannelServiceInterface $channelService,
    ) {}

    public function __invoke(Nutgram $bot): void
    {
        $userId = $bot->userId();

        $missingChannels = $this->channelService->getMissingChannelsFresh($bot, $userId);

        if (empty($missingChannels)) {
            $bot->answerCallbackQuery(
                text: __('nutgram-admin-panel::channel.messages.membership_verified'),
            );

            try {
                $bot->deleteMessage(
                    $bot->chatId(),
                    $bot->callbackQuery()->message->message_id,
                );
            } catch (\Throwable) {
                // Ignore errors when deleting the prompt message
                // (e.g., message already deleted, bot lacks permission)
            }

            return;
        }

        $bot->answerCallbackQuery(
            text: __('nutgram-admin-panel::channel.messages.still_not_member'),
            show_alert: true,
        );

        $this->channelService->sendJoinPrompt($bot, $missingChannels);
    }
}
