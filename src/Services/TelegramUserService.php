<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Services;

use AbdullajonovLab\NutgramAdminPanel\Contracts\TelegramUserServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use SergiX44\Nutgram\Telegram\Types\User\User;

class TelegramUserService implements TelegramUserServiceInterface
{
    public function persistFromTelegram(User $telegramUser, bool $isStartCommand = false): BotUser
    {
        $botUser = BotUser::updateOrCreate(
            ['telegram_id' => $telegramUser->id],
            [
                'first_name' => $telegramUser->first_name,
                'last_name' => $telegramUser->last_name,
                'username' => $telegramUser->username,
                'language_code' => $telegramUser->language_code,
                'last_activity_at' => now(),
            ]
        );

        if ($botUser->wasRecentlyCreated) {
            $botUser->refresh();
        }

        if ($isStartCommand && $botUser->status !== UserStatus::Active) {
            $botUser->update(['status' => UserStatus::Active]);
        }

        return $botUser;
    }

    public function markBlocked(int $telegramId): void
    {
        BotUser::where('telegram_id', $telegramId)
            ->update(['status' => UserStatus::Blocked]);
    }

    public function markDeactivated(int $telegramId): void
    {
        BotUser::where('telegram_id', $telegramId)
            ->update(['status' => UserStatus::Deactivated]);
    }
}
