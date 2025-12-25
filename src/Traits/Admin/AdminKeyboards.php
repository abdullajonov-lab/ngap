<?php

namespace Ngap\Traits\Admin;

use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

trait AdminKeyboards
{
    protected function adminMainKeyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(__('ngap::admin_panel_keyboards.stats')),
                KeyboardButton::make(__('ngap::admin_panel_keyboards.send_ad'))
            )
            ->addRow(
                KeyboardButton::make(__('ngap::admin_panel_keyboards.manage_admin')),
                KeyboardButton::make(__('ngap::admin_panel_keyboards.manage_channels'))
            );
    }

    protected function backKeyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(__('ngap::admin_panel_keyboards.back'))
            );
    }

    protected function confirmCancelKeyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(__('ngap::admin_panel_keyboards.confirm')),
                KeyboardButton::make(__('ngap::admin_panel_keyboards.cancel'))
            );
    }

    protected function adminManageKeyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(__('ngap::manage_admins.add_admin')),
                KeyboardButton::make(__('ngap::manage_admins.remove_admin'))
            )
            ->addRow(
                KeyboardButton::make(__('ngap::manage_admins.list_admins')),
                KeyboardButton::make(__('ngap::admin_panel_keyboards.back'))
            );
    }

    protected function channelManageKeyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(__('ngap::manage_channels.add_channel')),
                KeyboardButton::make(__('ngap::manage_channels.remove_channel'))
            )
            ->addRow(
                KeyboardButton::make(__('ngap::manage_channels.list_channels')),
                KeyboardButton::make(__('ngap::admin_panel_keyboards.back'))
            );
    }

    protected function adminListInlineKeyboard(array $admins): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($admins as $admin) {
            $text = $admin->name;
            if ($admin->is_main) {
                $text .= ' (Main)';
            }

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    text: $text,
                    callback_data: "admin:view:{$admin->telegram_id}"
                )
            );
        }

        return $keyboard;
    }

    protected function channelListInlineKeyboard(array $channels): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($channels as $channel) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    text: $channel->getDisplayName(),
                    url: $channel->getLink()
                ),
                InlineKeyboardButton::make(
                    text: '❌',
                    callback_data: "channel:remove:{$channel->id}"
                )
            );
        }

        return $keyboard;
    }
}
