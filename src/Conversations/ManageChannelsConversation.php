<?php

namespace Ngap\Conversations;

use Ngap\Models\Channel;
use Ngap\Traits\Admin\AdminHelpers;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class ManageChannelsConversation extends Conversation
{
    use AdminHelpers;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::manage_channels.menu'),
            reply_markup: $this->channelManageKeyboard()
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
            __('ngap::manage_channels.add_channel') => $this->addChannelStep($bot),
            __('ngap::manage_channels.remove_channel') => $this->removeChannelStep($bot),
            __('ngap::manage_channels.list_channels') => $this->listChannels($bot),
            default => $this->invalidOption($bot),
        };
    }

    protected function addChannelStep(Nutgram $bot): void
    {
        if (!$this->hasPermission($bot->userId(), 'can_add_channel')) {
            $this->sendNoPermission($bot);
            $this->end();
            return;
        }

        $bot->sendMessage(
            text: __('ngap::manage_channels.enter_channel'),
            reply_markup: $this->backKeyboard()
        );

        $this->next('receiveChannel');
    }

    public function receiveChannel(Nutgram $bot): void
    {
        if ($this->isBackAction($bot)) {
            $this->start($bot);
            return;
        }

        $text = $bot->message()?->text;

        if (!$text) {
            $bot->sendMessage(
                text: __('ngap::manage_channels.invalid_channel'),
                reply_markup: $this->backKeyboard()
            );
            $this->next('receiveChannel');
            return;
        }

        // Try to get chat info
        try {
            $chatId = $text;

            // If it's a username, prefix with @
            if (!is_numeric($text) && !str_starts_with($text, '@')) {
                $chatId = '@' . $text;
            }

            $chat = $bot->getChat($chatId);

            if (!$chat) {
                throw new \Exception('Chat not found');
            }

            // Check if bot is admin in the channel
            $botMember = $bot->getChatMember(
                chat_id: $chat->id,
                user_id: $bot->getMe()->id
            );

            if (!$botMember || $botMember->status->value !== 'administrator') {
                $bot->sendMessage(
                    text: __('ngap::manage_channels.bot_not_admin'),
                    reply_markup: $this->backKeyboard()
                );
                $this->next('receiveChannel');
                return;
            }

            // Check if channel already exists
            if (Channel::findByChannelId($chat->id)) {
                $bot->sendMessage(
                    text: __('ngap::manage_channels.channel_exists'),
                    reply_markup: $this->channelManageKeyboard()
                );
                $this->next('handleAction');
                return;
            }

            // Add channel
            $channel = Channel::addChannel(
                channelId: $chat->id,
                username: $chat->username ?? '',
                title: $chat->title
            );

            $bot->sendMessage(
                text: __('ngap::manage_channels.channel_added', [
                    'title' => $channel->getDisplayName()
                ]),
                parse_mode: ParseMode::MARKDOWN,
                reply_markup: $this->channelManageKeyboard()
            );

            $this->next('handleAction');

        } catch (\Exception $e) {
            $bot->sendMessage(
                text: __('ngap::manage_channels.channel_not_found'),
                reply_markup: $this->backKeyboard()
            );
            $this->next('receiveChannel');
        }
    }

    protected function removeChannelStep(Nutgram $bot): void
    {
        if (!$this->hasPermission($bot->userId(), 'can_del_channel')) {
            $this->sendNoPermission($bot);
            $this->end();
            return;
        }

        $channels = Channel::active()->get();

        if ($channels->isEmpty()) {
            $bot->sendMessage(
                text: __('ngap::manage_channels.no_channels'),
                reply_markup: $this->channelManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $list = __('ngap::manage_channels.select_channel_to_remove') . "\n\n";
        foreach ($channels as $channel) {
            $list .= "• `{$channel->id}` - {$channel->getDisplayName()}\n";
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
                text: __('ngap::manage_channels.invalid_id'),
                reply_markup: $this->backKeyboard()
            );
            $this->next('receiveRemoveId');
            return;
        }

        $channel = Channel::find((int) $text);

        if (!$channel) {
            $bot->sendMessage(
                text: __('ngap::manage_channels.channel_not_found'),
                reply_markup: $this->channelManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $name = $channel->getDisplayName();
        $channel->delete();

        $bot->sendMessage(
            text: __('ngap::manage_channels.channel_removed', ['title' => $name]),
            reply_markup: $this->channelManageKeyboard()
        );

        $this->next('handleAction');
    }

    protected function listChannels(Nutgram $bot): void
    {
        $channels = Channel::active()->get();

        if ($channels->isEmpty()) {
            $bot->sendMessage(
                text: __('ngap::manage_channels.no_channels'),
                reply_markup: $this->channelManageKeyboard()
            );
            $this->next('handleAction');
            return;
        }

        $list = __('ngap::manage_channels.channel_list') . "\n\n";
        foreach ($channels as $channel) {
            $list .= "📢 {$channel->getDisplayName()}\n";
        }

        $bot->sendMessage(
            text: $list,
            reply_markup: $this->channelManageKeyboard()
        );

        $this->next('handleAction');
    }

    protected function invalidOption(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::main.invalid_option'),
            reply_markup: $this->channelManageKeyboard()
        );
        $this->next('handleAction');
    }
}
