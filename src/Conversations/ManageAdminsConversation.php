<?php

namespace Ngap\Conversations;

use Ngap\Models\Admin;
use Ngap\Traits\Admin\AdminHelpers;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class ManageAdminsConversation extends Conversation
{
    use AdminHelpers;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::manage_admins.menu'),
            reply_markup: $this->adminManageKeyboard()
        );

        $this->next('handleAction');
    }

    public function handleAction(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if ($this->isBackAction($bot)) {
            $this->sendBackToMain($bot);
            $this->end();
            return;
        }

        match ($text) {
            __('ngap::manage_admins.add_admin') => $this->addAdminStep($bot),
            __('ngap::manage_admins.remove_admin') => $this->removeAdminStep($bot),
            __('ngap::manage_admins.list_admins') => $this->listAdmins($bot),
            default => $this->invalidOption($bot),
        };
    }

    protected function addAdminStep(Nutgram $bot): void
    {
        if (!$this->hasPermission($bot->userId(), 'can_add_admin')) {
            $this->sendNoPermission($bot);
            $this->end();
            return;
        }

        $bot->sendMessage(
            text: __('ngap::manage_admins.enter_admin_id'),
            reply_markup: $this->backKeyboard()
        );

        $this->next('receiveAdminId');
    }

    public function receiveAdminId(Nutgram $bot): void
    {
        if ($this->isBackAction($bot)) {
            $this->start($bot);
            return;
        }

        $text = $bot->message()?->text;

        if (!$text || !is_numeric($text)) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.invalid_id'),
                reply_markup: $this->backKeyboard()
            );
            $this->next('receiveAdminId');
            return;
        }

        $telegramId = (int) $text;

        if (Admin::isAdmin($telegramId)) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.already_admin'),
                reply_markup: $this->adminManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $bot->sendMessage(
            text: __('ngap::manage_admins.enter_admin_name'),
            reply_markup: $this->backKeyboard()
        );

        $bot->setUserData('new_admin_id', $telegramId);
        $this->next('receiveAdminName');
    }

    public function receiveAdminName(Nutgram $bot): void
    {
        if ($this->isBackAction($bot)) {
            $this->start($bot);
            return;
        }

        $name = $bot->message()?->text;
        $telegramId = $bot->getUserData('new_admin_id');

        if (!$name || !$telegramId) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.invalid_name'),
                reply_markup: $this->backKeyboard()
            );
            $this->next('receiveAdminName');
            return;
        }

        $this->createAdmin((int) $telegramId, $name);

        $bot->sendMessage(
            text: __('ngap::manage_admins.admin_added', ['name' => $name, 'id' => $telegramId]),
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->adminManageKeyboard()
        );

        $bot->deleteUserData('new_admin_id');
        $this->next('handleAction');
    }

    protected function removeAdminStep(Nutgram $bot): void
    {
        if (!$this->hasPermission($bot->userId(), 'can_del_admin')) {
            $this->sendNoPermission($bot);
            $this->end();
            return;
        }

        $admins = Admin::where('is_main', false)->get();

        if ($admins->isEmpty()) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.no_admins_to_remove'),
                reply_markup: $this->adminManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $list = __('ngap::manage_admins.select_admin_to_remove') . "\n\n";
        foreach ($admins as $admin) {
            $list .= "• `{$admin->telegram_id}` - {$admin->name}\n";
        }

        $bot->sendMessage(
            text: $list,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->backKeyboard()
        );

        $this->next('receiveRemoveId');
    }

    public function receiveRemoveId(Nutgram $bot): void
    {
        if ($this->isBackAction($bot)) {
            $this->start($bot);
            return;
        }

        $text = $bot->message()?->text;

        if (!$text || !is_numeric($text)) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.invalid_id'),
                reply_markup: $this->backKeyboard()
            );
            $this->next('receiveRemoveId');
            return;
        }

        $telegramId = (int) $text;
        $admin = Admin::findByTelegramId($telegramId);

        if (!$admin) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.admin_not_found'),
                reply_markup: $this->adminManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        if ($admin->is_main) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.cannot_remove_main'),
                reply_markup: $this->adminManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $name = $admin->name;
        $this->deleteAdmin($telegramId);

        $bot->sendMessage(
            text: __('ngap::manage_admins.admin_removed', ['name' => $name]),
            reply_markup: $this->adminManageKeyboard()
        );

        $this->next('handleAction');
    }

    protected function listAdmins(Nutgram $bot): void
    {
        $admins = Admin::all();

        if ($admins->isEmpty()) {
            $bot->sendMessage(
                text: __('ngap::manage_admins.no_admins'),
                reply_markup: $this->adminManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $list = __('ngap::manage_admins.admin_list') . "\n\n";
        foreach ($admins as $admin) {
            $badge = $admin->is_main ? '👑' : '👤';
            $list .= "{$badge} `{$admin->telegram_id}` - {$admin->name}\n";
        }

        $bot->sendMessage(
            text: $list,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->adminManageKeyboard()
        );

        $this->next('handleAction');
    }

    protected function invalidOption(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::main.invalid_option'),
            reply_markup: $this->adminManageKeyboard()
        );
        $this->next('handleAction');
    }
}
