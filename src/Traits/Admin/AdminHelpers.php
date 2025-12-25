<?php

namespace Ngap\Traits\Admin;

use Ngap\Models\Admin;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

trait AdminHelpers
{
    use AdminKeyboards;

    protected function getAdmin(int $telegramId): ?Admin
    {
        return Admin::findByTelegramId($telegramId);
    }

    protected function getAdminName(int $telegramId): ?string
    {
        return $this->getAdmin($telegramId)?->name;
    }

    protected function createAdmin(int $telegramId, string $name, array $permissions = []): Admin
    {
        return Admin::createWithPermissions($telegramId, $name, $permissions);
    }

    protected function deleteAdmin(int $telegramId): bool
    {
        $admin = Admin::findByTelegramId($telegramId);

        if (!$admin) {
            return false;
        }

        if ($admin->is_main) {
            return false; // Cannot delete main admin
        }

        return (bool) $admin->delete();
    }

    protected function hasPermission(int $telegramId, string $permission): bool
    {
        $admin = $this->getAdmin($telegramId);
        return $admin?->hasPermission($permission) ?? false;
    }

    protected function isBackAction(Nutgram $bot): bool
    {
        $text = $bot->message()?->text;
        return $text === __('ngap::admin_panel_keyboards.back');
    }

    protected function isCancelAction(Nutgram $bot): bool
    {
        $text = $bot->message()?->text;
        return $text === __('ngap::admin_panel_keyboards.cancel');
    }

    protected function sendBackToMain(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::admin_panel.start_msg'),
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->adminMainKeyboard()
        );
    }

    protected function sendCancelled(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::main.action_cancelled'),
            reply_markup: $this->adminMainKeyboard()
        );
    }

    protected function sendNoPermission(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::admin_panel.no_permission'),
            reply_markup: $this->adminMainKeyboard()
        );
    }
}
