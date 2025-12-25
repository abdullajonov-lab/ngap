<?php

namespace Ngap\Middleware;

use Ngap\Models\Admin;
use SergiX44\Nutgram\Nutgram;

class CheckAdminPermission
{
    public function __construct(
        protected string $permission
    ) {}

    public function __invoke(Nutgram $bot, $next): void
    {
        $userId = $bot->userId();

        if (!$userId) {
            return;
        }

        $admin = Admin::findByTelegramId($userId);

        if (!$admin) {
            return;
        }

        if (!$admin->hasPermission($this->permission)) {
            $bot->sendMessage(__('ngap::admin_panel.no_permission'));
            return;
        }

        $next($bot);
    }
}
