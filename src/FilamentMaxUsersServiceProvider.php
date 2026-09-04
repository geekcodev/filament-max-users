<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers;

use Illuminate\Support\ServiceProvider;

class FilamentMaxUsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-max-users.php', 'filament-max-users');
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-max-users');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-max-users.php' => $this->app->configPath('filament-max-users.php'),
            ], 'filament-max-users-config');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/filament-max-users'),
            ], 'filament-max-users-lang');
        }
    }
}
