<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Contracts;

use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use SergiX44\Nutgram\Telegram\Types\User\User;

interface TelegramUserServiceInterface
{
    public function persistFromTelegram(User $telegramUser, bool $isStartCommand = false): BotUser;

    public function markBlocked(int $telegramId): void;

    public function markDeactivated(int $telegramId): void;
}
