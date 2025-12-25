<?php

namespace Ngap\Conversations;

use Ngap\Controllers\AdsController;
use Ngap\Models\Ads;
use Ngap\Models\User;
use Ngap\Traits\Admin\AdminHelpers;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class AdsConversation extends Conversation
{
    use AdminHelpers;

    protected ?int $messageId = null;
    protected ?int $fromChatId = null;

    public function start(Nutgram $bot): void
    {
        if (!$this->hasPermission($bot->userId(), 'can_send_ads')) {
            $this->sendNoPermission($bot);
            $this->end();
            return;
        }

        $bot->sendMessage(
            text: __('ngap::send_ads.start'),
            reply_markup: $this->backKeyboard()
        );

        $this->next('receiveAd');
    }

    public function receiveAd(Nutgram $bot): void
    {
        if ($this->isBackAction($bot) || $this->isCancelAction($bot)) {
            $this->sendBackToMain($bot);
            $this->end();
            return;
        }

        $message = $bot->message();
        if (!$message) {
            $bot->sendMessage(__('ngap::send_ads.invalid_message'));
            $this->next('receiveAd');
            return;
        }

        $this->messageId = $message->message_id;
        $this->fromChatId = $bot->chatId();

        // Show preview
        $bot->copyMessage(
            chat_id: $bot->chatId(),
            from_chat_id: $this->fromChatId,
            message_id: $this->messageId
        );

        $userCount = User::active()->count();

        $bot->sendMessage(
            text: __('ngap::send_ads.confirm_send', ['count' => $userCount]),
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->confirmCancelKeyboard()
        );

        $this->next('confirmSend');
    }

    public function confirmSend(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if ($this->isCancelAction($bot) || $this->isBackAction($bot)) {
            $this->sendCancelled($bot);
            $this->end();
            return;
        }

        if ($text !== __('ngap::admin_panel_keyboards.confirm')) {
            $bot->sendMessage(
                text: __('ngap::main.invalid_option'),
                reply_markup: $this->confirmCancelKeyboard()
            );
            $this->next('confirmSend');
            return;
        }

        $userCount = User::active()->count();

        if ($userCount === 0) {
            $bot->sendMessage(
                text: __('ngap::send_ads.no_users'),
                reply_markup: $this->adminMainKeyboard()
            );
            $this->end();
            return;
        }

        // Create ad record
        $ad = AdsController::create(
            adminTelegramId: $bot->userId(),
            messageId: $this->messageId,
            fromChatId: $this->fromChatId
        );

        $bot->sendMessage(
            text: __('ngap::send_ads.sending_started', ['count' => $userCount]),
            reply_markup: $this->adminMainKeyboard()
        );

        // Send ads
        $adsController = new AdsController($bot);
        $result = $adsController->send($ad);

        // Report results
        $bot->sendMessage(
            text: __('ngap::send_ads.sending_completed', [
                'sent' => $result['sent'],
                'failed' => $result['failed'],
                'rate' => $result['success_rate'] ?? 0,
            ]),
            parse_mode: ParseMode::MARKDOWN
        );

        $this->end();
    }
}
