<?php

namespace Ngap\Conversations;

use Ngap\Controllers\StatisticsController;
use Ngap\Traits\Admin\AdminHelpers;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class StatisticsConversation extends Conversation
{
    use AdminHelpers;

    public function start(Nutgram $bot): void
    {
        if (!$this->hasPermission($bot->userId(), 'can_view_stats')) {
            $this->sendNoPermission($bot);
            $this->end();
            return;
        }

        $stats = StatisticsController::getFormattedStats();

        $bot->sendMessage(
            text: $stats,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->backKeyboard()
        );

        $this->next('waitForBack');
    }

    public function waitForBack(Nutgram $bot): void
    {
        if ($this->isBackAction($bot)) {
            $this->sendBackToMain($bot);
            $this->end();
            return;
        }

        // Refresh stats if user sends anything else
        $stats = StatisticsController::getFormattedStats();

        $bot->sendMessage(
            text: $stats,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->backKeyboard()
        );

        $this->next('waitForBack');
    }
}
