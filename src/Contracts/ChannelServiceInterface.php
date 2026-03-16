<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Contracts;

use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use SergiX44\Nutgram\Nutgram;

interface ChannelServiceInterface
{
    /**
     * Validate a channel exists and the bot is an admin.
     *
     * @param Nutgram $bot
     * @param string $chatIdOrUsername Chat ID or @username
     * @return array{chat_id: int, title: ?string, username: ?string, member_count: ?int}
     * @throws \InvalidArgumentException If channel not found or bot is not admin
     */
    public function validateChannel(Nutgram $bot, string $chatIdOrUsername): array;

    /**
     * Check if a user is a member of a channel.
     *
     * Handles ChatMemberRestricted by checking the is_member field.
     * Returns false for left, kicked, or null results.
     *
     * @param Nutgram $bot
     * @param int|string $chatId
     * @param int $userId
     * @return bool
     */
    public function isUserMember(Nutgram $bot, int|string $chatId, int $userId): bool;

    /**
     * Get mandatory active channels the user has NOT joined.
     *
     * Results are cached with configurable TTL.
     * Telegram API errors are caught and reported -- the channel is skipped
     * (graceful degradation: user is allowed through).
     *
     * @param Nutgram $bot
     * @param int $userId
     * @return Channel[] Array of Channel models the user is not a member of
     */
    public function getMissingChannels(Nutgram $bot, int $userId): array;

    /**
     * Same as getMissingChannels but bypasses cache for fresh results.
     *
     * Used by the "Check membership" button to get real-time data.
     *
     * @param Nutgram $bot
     * @param int $userId
     * @return Channel[] Array of Channel models the user is not a member of
     */
    public function getMissingChannelsFresh(Nutgram $bot, int $userId): array;

    /**
     * Send a join prompt with inline keyboard buttons linking to channels.
     *
     * Builds an InlineKeyboardMarkup with one URL button per channel
     * plus a final "Check membership" callback button.
     *
     * @param Nutgram $bot
     * @param Channel[] $channels
     * @return void
     */
    public function sendJoinPrompt(Nutgram $bot, array $channels): void;

    /**
     * Sync member count from Telegram API and update the channel model.
     *
     * @param Nutgram $bot
     * @param Channel $channel
     * @return int Updated member count
     */
    public function syncMemberCount(Nutgram $bot, Channel $channel): int;
}
