<?php

namespace Ngap\Middleware;

use Ngap\Models\Admin;
use SergiX44\Nutgram\Nutgram;

class CheckAdmin
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $userId = $bot->userId();

        if (!$userId) {
            return;
        }

        if (!Admin::isAdmin($userId)) {
            return;
        }

        $next($bot);
    }
}
