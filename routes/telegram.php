<?php

/** @var SergiX44\Nutgram\Nutgram $bot */

use AbdullajonovLab\NutgramAdminPanel\Middleware\ChannelJoinMiddleware;
use AbdullajonovLab\NutgramAdminPanel\Middleware\PersistUserMiddleware;
use AbdullajonovLab\NutgramAdminPanel\Handlers\CheckMembershipHandler;

/*
|--------------------------------------------------------------------------
| Nutgram Admin Panel Routes
|--------------------------------------------------------------------------
|
| These routes are registered by the nutgram-admin-panel package.
| PersistUserMiddleware tracks user data on every interaction.
| ChannelJoinMiddleware enforces mandatory channel membership.
|
*/

$bot->middleware(PersistUserMiddleware::class);
$bot->middleware(ChannelJoinMiddleware::class);

$bot->onCallbackQueryData('check_membership', CheckMembershipHandler::class);

/*
|--------------------------------------------------------------------------
| Default Handlers
|--------------------------------------------------------------------------
|
| Basic bot responses so users get feedback out of the box.
| Override these in your published routes/telegram.php.
|
*/

$bot->onCommand('start', function (SergiX44\Nutgram\Nutgram $bot) {
    $bot->sendMessage('Welcome! The bot is up and running.');
});

$bot->onCommand('help', function (SergiX44\Nutgram\Nutgram $bot) {
    $bot->sendMessage("Available commands:\n/start - Start the bot\n/help - Show this help message");
});

$bot->fallback(function (SergiX44\Nutgram\Nutgram $bot) {
    $bot->sendMessage('Sorry, I don\'t understand that command. Try /help.');
});
