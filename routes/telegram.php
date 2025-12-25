<?php

/**
 * NGAP Telegram Routes
 *
 * This file defines all bot command handlers and message listeners.
 * You can publish this file to customize the bot behavior.
 *
 * Usage: php artisan vendor:publish --tag=ngap-telegram
 */

use Ngap\Commands\AdminCommand;
use Ngap\Commands\StartCommand;
use Ngap\Conversations\AdsConversation;
use Ngap\Conversations\CheckSubsConversation;
use Ngap\Conversations\ManageAdminsConversation;
use Ngap\Conversations\ManageChannelsConversation;
use Ngap\Conversations\StatisticsConversation;
use Ngap\Middleware\CheckAdmin;
use Ngap\Middleware\CheckAdminPermission;
use Ngap\Middleware\CheckSubscription;
use Ngap\Middleware\RegisterUser;
use SergiX44\Nutgram\Nutgram;

/** @var Nutgram $bot */
$bot = app(Nutgram::class);

/*
|--------------------------------------------------------------------------
| User Commands
|--------------------------------------------------------------------------
*/

$bot->onCommand('start', StartCommand::class)
    ->middleware(RegisterUser::class)
    ->middleware(CheckSubscription::class);

/*
|--------------------------------------------------------------------------
| Admin Commands
|--------------------------------------------------------------------------
*/

$bot->onCommand('admin', AdminCommand::class)
    ->middleware(CheckAdmin::class);

/*
|--------------------------------------------------------------------------
| Admin Panel - Statistics
|--------------------------------------------------------------------------
*/

$bot->onMessage(StatisticsConversation::class)
    ->middleware(CheckAdmin::class)
    ->middleware(function (Nutgram $bot, $next) {
        if ($bot->message()?->text === __('ngap::admin_panel_keyboards.stats')) {
            $next($bot);
        }
    });

/*
|--------------------------------------------------------------------------
| Admin Panel - Send Ads
|--------------------------------------------------------------------------
*/

$bot->onMessage(AdsConversation::class)
    ->middleware(CheckAdmin::class)
    ->middleware(function (Nutgram $bot, $next) {
        if ($bot->message()?->text === __('ngap::admin_panel_keyboards.send_ad')) {
            $next($bot);
        }
    });

/*
|--------------------------------------------------------------------------
| Admin Panel - Manage Admins
|--------------------------------------------------------------------------
*/

$bot->onMessage(ManageAdminsConversation::class)
    ->middleware(CheckAdmin::class)
    ->middleware(function (Nutgram $bot, $next) {
        if ($bot->message()?->text === __('ngap::admin_panel_keyboards.manage_admin')) {
            $next($bot);
        }
    });

/*
|--------------------------------------------------------------------------
| Admin Panel - Manage Channels
|--------------------------------------------------------------------------
*/

$bot->onMessage(ManageChannelsConversation::class)
    ->middleware(CheckAdmin::class)
    ->middleware(function (Nutgram $bot, $next) {
        if ($bot->message()?->text === __('ngap::admin_panel_keyboards.manage_channels')) {
            $next($bot);
        }
    });

/*
|--------------------------------------------------------------------------
| Callback Queries
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('check_subs:{slug}', CheckSubsConversation::class);
