<?php

namespace Ngap\Providers;

use Illuminate\Support\ServiceProvider;
use SergiX44\Nutgram\Nutgram;

class NgapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ngap.php', 'ngap');

        // Defer Nutgram instantiation - only create when actually needed
        $this->app->singleton(Nutgram::class, function ($app) {
            $token = config('ngap.telegram.token');

            if (empty($token)) {
                throw new \InvalidArgumentException(
                    'Telegram bot token is not configured. Please set TELEGRAM_BOT_TOKEN in your .env file.'
                );
            }

            return new Nutgram($token);
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerTranslations();

        // Only register routes when not running in console or during package discovery
        if (!$this->app->runningInConsole()) {
            $this->registerRoutes();
        }
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../../config/ngap.php' => config_path('ngap.php'),
            ], 'ngap-config');

            // Migrations
            $this->publishes([
                __DIR__ . '/../../database/migrations/' => database_path('migrations'),
            ], 'ngap-migrations');

            // Language files
            $this->publishes([
                __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/ngap'),
            ], 'ngap-lang');

            // Telegram routes
            $this->publishes([
                __DIR__ . '/../../routes/telegram.php' => base_path('routes/telegram.php'),
            ], 'ngap-telegram');

            // All at once
            $this->publishes([
                __DIR__ . '/../../config/ngap.php' => config_path('ngap.php'),
                __DIR__ . '/../../database/migrations/' => database_path('migrations'),
                __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/ngap'),
            ], 'ngap');
        }
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'ngap');
    }

    protected function registerRoutes(): void
    {
        $packageRoutes = __DIR__ . '/../../routes/telegram.php';
        if (file_exists($packageRoutes)) {
            require $packageRoutes;
        }
    }
}
