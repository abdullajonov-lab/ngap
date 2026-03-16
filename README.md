# Nutgram Admin Panel

A [FilamentPHP](https://filamentphp.com) plugin that provides a full-featured admin panel for managing Telegram bots built with [Nutgram](https://github.com/nutgram/nutgram).

## Features

- **User Tracking** — Automatically tracks bot users (name, username, language, status, last activity)
- **Channel Management** — Add channels/groups, enforce mandatory join before bot usage, sync member counts
- **Admin Management** — Manage bot administrators with role-based access (Admin / Super Admin)
- **Broadcast System** — Send messages to all active users with rate limiting, progress tracking, and cancellation
- **Statistics Dashboard** — Real-time stats with charts for user growth, retention, broadcast delivery, and channel membership

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- FilamentPHP 3.x
- [nutgram/laravel](https://github.com/nutgram/laravel) 1.6+
- Redis (required for broadcast rate limiting)
- A queue worker (for broadcasts)

## Installation

```bash
composer require abdullajonov-lab/nutgram-admin-panel
```

Run the migrations:

```bash
php artisan migrate
```

### Register the Plugin

Add the plugin to your Filament panel provider (e.g. `app/Providers/Filament/AdminPanelProvider.php`):

```php
use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            NutgramAdminPanelPlugin::make(),
        ]);
}
```

> **Important:** Use `->plugins([...])` (plural, array), not `->plugin(...)`.

### Remove Default Dashboard (Recommended)

The plugin registers a Statistics page as the landing page (`/`). To avoid route conflicts, remove the default Filament dashboard:

```php
use Filament\Pages\Dashboard;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->pages([
            // Remove Dashboard::class or leave empty
        ])
        ->plugins([
            NutgramAdminPanelPlugin::make(),
        ]);
}
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=nutgram-admin-panel-config
```

```php
// config/nutgram-admin-panel.php

return [
    // Customize table names to avoid conflicts
    'table_names' => [
        'bot_users'  => 'nutgram_bot_users',
        'channels'   => 'nutgram_channels',
        'broadcasts' => 'nutgram_broadcasts',
        'admins'     => 'nutgram_admins',
    ],

    'broadcast' => [
        'queue'      => env('NUTGRAM_BROADCAST_QUEUE', 'default'),
        'rate_limit' => 25, // messages/second (Telegram limit ~30)
        'max_tries'  => 3,
    ],

    'channel_check' => [
        'enabled'   => true,
        'cache_ttl' => 300, // seconds
    ],
];
```

## Telegram Bot Routes

The package registers default bot routes (middleware, handlers, and basic commands). To customize them, publish the route file:

```bash
php artisan vendor:publish --tag=nutgram-admin-panel-routes
```

This creates `routes/telegram.php` in your project. The package will use your published file instead of the default one.

The default routes include:

```php
// Middleware (runs on every update)
$bot->middleware(PersistUserMiddleware::class);   // Tracks users
$bot->middleware(ChannelJoinMiddleware::class);   // Enforces mandatory channel join

// Handler for "Check membership" button
$bot->onCallbackQueryData('check_membership', CheckMembershipHandler::class);

// Default commands
$bot->onCommand('start', fn (Nutgram $bot) => $bot->sendMessage('Welcome!'));
$bot->onCommand('help', fn (Nutgram $bot) => $bot->sendMessage('...'));
$bot->fallback(fn (Nutgram $bot) => $bot->sendMessage('Try /help'));
```

## Usage

### User Tracking

User tracking is automatic. The `PersistUserMiddleware` runs on every bot update and saves/updates user data in the `nutgram_bot_users` table.

Tracked fields:
- `telegram_id`, `first_name`, `last_name`, `username`, `language_code`
- `status` — `active`, `blocked`, or `deactivated`
- `last_activity_at` — updated on every interaction

Users are listed in the admin panel under **Bot Management > Bot Users** (read-only).

### Channel Management

#### Adding a Channel

1. Go to **Bot Management > Channels > Create**
2. Enter the channel's `chat_id` (numeric, e.g. `-1001234567890`) or `@username`
3. The bot will validate it has admin access to the channel
4. Toggle **Is Mandatory** to enforce joining before bot usage
5. Toggle **Is Active** to enable/disable the channel

> The bot must be an **administrator** in the channel for validation and membership checks to work.

#### Mandatory Join Flow

When `channel_check.enabled` is `true`:

1. User sends a message to the bot
2. `ChannelJoinMiddleware` checks if the user is a member of all mandatory channels
3. If not, the bot sends a prompt with join links and a "Check membership" button
4. User joins the channels and presses "Check membership"
5. If verified, the prompt is deleted and the bot processes normally

Membership results are cached for `cache_ttl` seconds (default: 5 minutes).

### Admin Management

Go to **Bot Management > Admins** to manage bot administrators.

Fields:
- `telegram_id` — The admin's Telegram user ID
- `name` — Display name
- `role` — `admin` or `super_admin`

The last remaining admin cannot be deleted (safety check).

### Broadcasts

#### Sending a Broadcast

1. Go to **Bot Management > Broadcasts > Create**
2. Write your message (HTML formatting supported)
3. Submit — the broadcast is queued and sent to all active users

#### Broadcast Lifecycle

| Status      | Description                        |
|-------------|------------------------------------|
| `pending`   | Created, waiting for queue worker  |
| `sending`   | Currently delivering messages      |
| `completed` | All messages sent                  |
| `cancelled` | Manually cancelled during sending  |
| `failed`    | Failed to complete                 |

The broadcast list auto-refreshes every 5 seconds so you can monitor progress in real time. You can cancel a broadcast while it's `sending`.

#### Queue Worker

Broadcasts require a running queue worker:

```bash
php artisan queue:work --queue=default
```

Or if you configured a custom queue:

```bash
php artisan queue:work --queue=broadcasts
```

### Statistics Dashboard

The landing page shows 5 widgets:

1. **Bot Stats Overview** — Total users, active users, blocked users, total broadcasts (refreshes every 10s)
2. **User Growth Chart** — Line chart with week/month/year filters (30s refresh)
3. **User Retention Chart** — Returning users over time, line chart (30s refresh)
4. **Broadcast Delivery Chart** — Stacked bar chart showing sent/failed/blocked per broadcast (30s refresh)
5. **Channel Membership Chart** — Horizontal bar chart comparing member counts across channels (30s refresh)

## Customizing Services

All services are bound to interfaces in the container. You can swap any implementation:

```php
use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use App\Services\MyCustomChannelService;

// In a service provider
$this->app->bind(ChannelServiceInterface::class, MyCustomChannelService::class);
```

Available interfaces:

| Interface                      | Default Implementation  | Purpose                          |
|--------------------------------|-------------------------|----------------------------------|
| `TelegramUserServiceInterface` | `TelegramUserService`   | User persistence and status      |
| `ChannelServiceInterface`      | `ChannelService`        | Channel validation and membership|
| `BroadcastServiceInterface`    | `BroadcastService`      | Broadcast creation and delivery  |
| `StatisticsServiceInterface`   | `StatisticsService`     | Dashboard data aggregation       |

## Translations

The package supports localization. Publish the translation files:

```bash
php artisan vendor:publish --tag=nutgram-admin-panel-translations
```

Translation files are organized by feature: `user.php`, `channel.php`, `admin.php`, `broadcast.php`, `statistics.php`, `general.php`.

## Running the Bot

### Polling (Development)

```bash
php artisan nutgram:run
```

### Webhook (Production)

```bash
php artisan nutgram:hook:set https://your-domain.com/webhook
```

See the [Nutgram Laravel documentation](https://github.com/nutgram/laravel) for more details.

## Testing

```bash
composer test
```

## License

MIT
