<?php

namespace Ngap\Commands;

use Ngap\Traits\Admin\AdminKeyboards;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class AdminCommand extends Command
{
    use AdminKeyboards;

    protected string $command = 'admin';
    protected ?string $description = 'Open admin panel';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('ngap::admin_panel.start_msg'),
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $this->adminMainKeyboard()
        );
    }
}
