<?php

namespace Ngap\Conversations;

use Ngap\Models\Channel;
use Ngap\Traits\User\CheckSubscriptionTrait;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class CheckSubsConversation extends Conversation
{
    use CheckSubscriptionTrait;

    public function start(Nutgram $bot): void
    {
        $userId = $bot->userId();
        $channels = Channel::getActiveChannels();

        if ($channels->isEmpty()) {
            $bot->answerCallbackQuery(
                text: __('ngap::subs.no_channels_required'),
                show_alert: true
            );
            return;
        }

        $unsubscribedChannels = $this->getUnsubscribedChannels($bot, $userId, $channels);

        if ($unsubscribedChannels->isEmpty()) {
            // User is subscribed to all channels
            $bot->answerCallbackQuery(
                text: __('ngap::subs.subscription_verified'),
                show_alert: true
            );

            // Delete the subscription message
            try {
                $bot->deleteMessage(
                    chat_id: $bot->chatId(),
                    message_id: $bot->messageId()
                );
            } catch (\Exception $e) {
                // Message might already be deleted
            }

            // Send welcome message
            $bot->sendMessage(
                text: __('ngap::subs.welcome_after_subscribe')
            );

            $this->end();
            return;
        }

        // Still not subscribed to all channels
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($unsubscribedChannels as $channel) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    text: $channel->getDisplayName(),
                    url: $channel->getLink()
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: __('ngap::subs.check_button'),
                callback_data: 'check_subs:verify'
            )
        );

        try {
            $bot->editMessageText(
                text: __('ngap::subs.still_not_subscribed', [
                    'count' => $unsubscribedChannels->count()
                ]),
                chat_id: $bot->chatId(),
                message_id: $bot->messageId(),
                reply_markup: $keyboard
            );
        } catch (\Exception $e) {
            // Message might not be editable
        }

        $bot->answerCallbackQuery(
            text: __('ngap::subs.not_subscribed_alert'),
            show_alert: true
        );

        $this->end();
    }
}
