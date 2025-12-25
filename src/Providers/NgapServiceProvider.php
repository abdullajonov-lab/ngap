<?php

namespace Ngap\Providers;

use Illuminate\Support\ServiceProvider;
use Ngap\Models\Admin;
use SergiX44\Nutgram\Nutgram;

class NgapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ngap.php', 'ngap');

        $this->app->singleton(Nutgram::class, function ($app) {
            return new Nutgram(config('ngap.telegram.token'));
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerMainAdmin();
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
        // Load telegram routes if the file exists in the package
        $packageRoutes = __DIR__ . '/../../routes/telegram.php';
        if (file_exists($packageRoutes)) {
            require $packageRoutes;
        }
    }

    protected function registerMainAdmin(): void
    {
        $mainAdminId = config('ngap.admin.main_admin_id');
        if ($mainAdminId && $this->app->runningInConsole() === false) {
            try {
                if (Admin::count() === 0) {
                    Admin::createMainAdmin((int) $mainAdminId, 'Main Admin');
                }
            } catch (\Exception $e) {
                // Table might not exist yet
            }
        }
    }
}
