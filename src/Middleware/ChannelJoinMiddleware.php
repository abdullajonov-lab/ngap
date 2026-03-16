<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Middleware;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use SergiX44\Nutgram\Nutgram;

class ChannelJoinMiddleware
{
    public function __construct(
        private readonly ChannelServiceInterface $channelService,
    ) {}

    public function __invoke(Nutgram $bot, $next): void
    {
        if (! config('nutgram-admin-panel.channel_check.enabled')) {
            $next($bot);

            return;
        }

        if ($bot->userId() === null) {
            $next($bot);

            return;
        }

        // Allow the "Check membership" callback through so the handler can verify
        if ($bot->callbackQuery()?->data === 'check_membership') {
            $next($bot);

            return;
        }

        $missingChannels = $this->channelService->getMissingChannels($bot, $bot->userId());

        if (empty($missingChannels)) {
            $next($bot);

            return;
        }

        $this->channelService->sendJoinPrompt($bot, $missingChannels);
    }
}
