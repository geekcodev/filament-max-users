<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests;

use GeekCo\FilamentMaxUsers\FilamentMaxUsersServiceProvider;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\AdminPanelProvider;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\LaravelMaxClient\MaxServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../vendor/geekcodev/laravel-max-client/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/Migrations');

        DB::connection()->getPdo()->exec('PRAGMA foreign_keys = ON');

        Queue::fake();

        Gate::define(
            'users.view',
            static fn (?TestUser $user): bool => $user->can_view_users ?? false,
        );
        Gate::define(
            'users.manage',
            static fn (?TestUser $user): bool => $user->can_manage_users ?? false,
        );
        Gate::define(
            'chats.view',
            static fn (?TestUser $user): bool => $user->can_view_chats ?? false,
        );
        Gate::define(
            'chats.delete',
            static fn (?TestUser $user): bool => $user->can_delete_chats ?? false,
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Widgets\WidgetsServiceProvider::class,
            MaxServiceProvider::class,
            FilamentMaxUsersServiceProvider::class,
            AdminPanelProvider::class,

            // Livewire -- строго после Filament: SupportServiceProvider перебивает
            // биндинг DataStore non-shared bind(), а LivewireServiceProvider::register()
            // закрепляет механизмы через instance(). Иначе хранилище состояния Livewire
            // теряется между вызовами.
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('laravel-max-client.api_token', 'test-token');
        $app['config']->set('laravel-max-client.retry.base_delay_seconds', 0.0);
    }
}
