<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Services;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Properties\ChatMemberStatus;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberRestricted;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ChannelService implements ChannelServiceInterface
{
    public function validateChannel(Nutgram $bot, string $chatIdOrUsername): array
    {
        $chat = $bot->getChat($chatIdOrUsername);

        if ($chat === null) {
            throw new InvalidArgumentException(
                __('nutgram-admin-panel::channel.messages.channel_not_found')
            );
        }

        $me = $bot->getMe();
        $botMember = $bot->getChatMember($chat->id, $me->id);

        $isAdmin = $botMember !== null && in_array($botMember->status, [
            ChatMemberStatus::CREATOR,
            ChatMemberStatus::ADMINISTRATOR,
        ], true);

        if (! $isAdmin) {
            throw new InvalidArgumentException(
                __('nutgram-admin-panel::channel.messages.bot_not_admin')
            );
        }

        $memberCount = $bot->getChatMemberCount($chat->id);

        // Get invite link for private channels (no username)
        $inviteLink = null;
        if (! $chat->username) {
            try {
                $link = $bot->createChatInviteLink($chat->id);
                $inviteLink = $link->invite_link;
            } catch (\Throwable) {
                // Bot may not have permission to create invite links
            }
        }

        return [
            'chat_id' => $chat->id,
            'title' => $chat->title,
            'username' => $chat->username,
            'member_count' => $memberCount,
            'invite_link' => $inviteLink,
        ];
    }

    public function isUserMember(Nutgram $bot, int|string $chatId, int $userId): bool
    {
        $member = $bot->getChatMember($chatId, $userId);

        if ($member === null) {
            return false;
        }

        // Handle restricted users by checking the is_member field
        if ($member instanceof ChatMemberRestricted) {
            return $member->is_member;
        }

        // Creator, administrator, and member statuses indicate membership
        return in_array($member->status, [
            ChatMemberStatus::CREATOR,
            ChatMemberStatus::ADMINISTRATOR,
            ChatMemberStatus::MEMBER,
        ], true);
    }

    public function getMissingChannels(Nutgram $bot, int $userId): array
    {
        return $this->checkMissingChannels($bot, $userId, useCache: true);
    }

    public function getMissingChannelsFresh(Nutgram $bot, int $userId): array
    {
        return $this->checkMissingChannels($bot, $userId, useCache: false);
    }

    public function sendJoinPrompt(Nutgram $bot, array $channels): void
    {
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($channels as $channel) {
            if ($channel->username) {
                $url = "https://t.me/{$channel->username}";
            } elseif ($channel->invite_link) {
                $url = $channel->invite_link;
            } else {
                // Try to generate an invite link on the fly
                try {
                    $link = $bot->createChatInviteLink($channel->chat_id);
                    $url = $link->invite_link;
                    $channel->update(['invite_link' => $url]);
                } catch (\Throwable) {
                    // Skip channels we can't link to
                    continue;
                }
            }

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    text: $channel->title ?? (string) $channel->chat_id,
                    url: $url,
                )
            );
        }

        // Add "Check membership" button as the final row
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: __('nutgram-admin-panel::channel.messages.check_membership'),
                callback_data: 'check_membership',
            )
        );

        $bot->sendMessage(
            text: __('nutgram-admin-panel::channel.messages.join_required'),
            reply_markup: $keyboard,
        );
    }

    public function syncMemberCount(Nutgram $bot, Channel $channel): int
    {
        $count = $bot->getChatMemberCount($channel->chat_id);

        $channel->update(['member_count' => $count]);

        return $count;
    }

    /**
     * Internal method to check missing channels with or without cache.
     *
     * @param Nutgram $bot
     * @param int $userId
     * @param bool $useCache
     * @return Channel[]
     */
    private function checkMissingChannels(Nutgram $bot, int $userId, bool $useCache): array
    {
        $channels = Channel::where('is_mandatory', true)
            ->where('is_active', true)
            ->get();

        $ttl = (int) config('nutgram-admin-panel.channel_check.cache_ttl', 300);
        $missing = [];

        foreach ($channels as $channel) {
            $cacheKey = "nutgram:channel_member:{$channel->chat_id}:{$userId}";

            try {
                if ($useCache) {
                    $isMember = Cache::remember($cacheKey, $ttl, function () use ($bot, $channel, $userId) {
                        return $this->isUserMember($bot, $channel->chat_id, $userId);
                    });
                } else {
                    // Bypass cache: forget old value, check fresh, then store
                    Cache::forget($cacheKey);
                    $isMember = $this->isUserMember($bot, $channel->chat_id, $userId);
                    Cache::put($cacheKey, $isMember, $ttl);
                }

                if (! $isMember) {
                    $missing[] = $channel;
                }
            } catch (TelegramException $e) {
                // Graceful degradation: report the error and skip this channel
                // (user is allowed through)
                report($e);

                continue;
            }
        }

        return $missing;
    }
}
