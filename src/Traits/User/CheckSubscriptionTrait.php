<?php

namespace Ngap\Traits\User;

use Illuminate\Support\Collection;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatMemberStatus;

trait CheckSubscriptionTrait
{
    protected function isUserSubscribed(Nutgram $bot, int $userId, int $channelId): bool
    {
        try {
            $member = $bot->getChatMember(
                chat_id: $channelId,
                user_id: $userId
            );

            if (!$member) {
                return false;
            }

            $allowedStatuses = [
                ChatMemberStatus::CREATOR,
                ChatMemberStatus::ADMINISTRATOR,
                ChatMemberStatus::MEMBER,
            ];

            return in_array($member->status, $allowedStatuses, true);
        } catch (\Exception $e) {
            // If we can't check, assume not subscribed
            return false;
        }
    }

    protected function checkAllSubscriptions(Nutgram $bot, int $userId, Collection $channels): bool
    {
        foreach ($channels as $channel) {
            if (!$this->isUserSubscribed($bot, $userId, $channel->channel_id)) {
                return false;
            }
        }

        return true;
    }

    protected function getUnsubscribedChannels(Nutgram $bot, int $userId, Collection $channels): Collection
    {
        return $channels->filter(function ($channel) use ($bot, $userId) {
            return !$this->isUserSubscribed($bot, $userId, $channel->channel_id);
        });
    }
}
