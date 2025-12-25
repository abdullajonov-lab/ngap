<?php

namespace Ngap\Middleware;

use Ngap\Models\Admin;
use Ngap\Models\Channel;
use Ngap\Traits\User\CheckSubscriptionTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class CheckSubscription
{
    use CheckSubscriptionTrait;

    public function __invoke(Nutgram $bot, $next): void
    {
        // Check if subscription checking is enabled
        if (!config('ngap.subscription.enabled', true)) {
            $next($bot);
            return;
        }

        $userId = $bot->userId();

        if (!$userId) {
            return;
        }

        // Admins bypass subscription check
        if (Admin::isAdmin($userId)) {
            $next($bot);
            return;
        }

        $channels = Channel::getActiveChannels();

        // No channels configured, allow access
        if ($channels->isEmpty()) {
            $next($bot);
            return;
        }

        $unsubscribedChannels = $this->getUnsubscribedChannels($bot, $userId, $channels);

        if ($unsubscribedChannels->isEmpty()) {
            $next($bot);
            return;
        }

        // User is not subscribed to all channels
        $this->sendSubscriptionRequired($bot, $unsubscribedChannels);
    }

    protected function sendSubscriptionRequired(Nutgram $bot, $unsubscribedChannels): void
    {
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

        $bot->sendMessage(
            text: __('ngap::subs.subscribe_required'),
            reply_markup: $keyboard
        );
    }
}
