<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests;

use GeekCo\FilamentMaxUsers\FilamentMaxUsersServiceProvider;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\AdminPanelProvider;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\MockHttpClient;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\LaravelMaxClient\MaxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\TestCase as Orchestra;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Реестр max_users/max_chats/max_chat_users: провайдер пакета только
        // регистрирует путь миграций, поэтому прогоняем их явно.
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
            'chats.manage',
            static fn (?TestUser $user): bool => $user->can_manage_chats ?? false,
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

    /**
     * Ответ getChat(): диалог либо группа/канал.
     *
     * @param array<string, mixed> $overrides
     */
    protected function chatResponse(int $chatId, string $type = 'chat', array $overrides = []): ResponseInterface
    {
        $data = array_replace([
            'chat_id' => $chatId,
            'type' => $type,
            'status' => 'active',
            'last_event_time' => 1700000000000,
            'participants_count' => 2,
            'is_public' => false,
            'title' => null,
            'icon' => null,
            'owner_id' => null,
            'participants' => null,
            'link' => null,
            'description' => null,
            'dialog_with_user' => null,
            'messages_count' => null,
            'pinned_message' => null,
        ], $overrides);

        return $this->jsonResponse($data);
    }

    /**
     * Ответ getChatMembers(): полный профиль с аватаром.
     *
     * @param array<string, mixed> $overrides
     */
    protected function chatMemberResponse(int $userId, array $overrides = []): ResponseInterface
    {
        return $this->jsonResponse([
            'members' => [array_replace([
                'user_id' => $userId,
                'first_name' => 'Тест',
                'last_name' => 'Пользователь',
                'username' => 'testuser',
                'is_bot' => false,
                'last_activity_time' => 1700000000000,
                'name' => 'Тест Пользователь',
                'description' => null,
                'avatar_url' => 'https://avatars.example/'.$userId.'_s.jpg',
                'full_avatar_url' => 'https://avatars.example/'.$userId.'.jpg',
                'last_access_time' => 1700000000000,
                'is_owner' => false,
                'is_admin' => false,
                'join_time' => 1700000000,
                'permissions' => null,
                'alias' => null,
            ], $overrides)],
        ]);
    }

    /**
     * @param array<mixed> $data
     */
    protected function jsonResponse(array $data): ResponseInterface
    {
        return new Response(200, [], json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * Подменить транспорт MAX API: сервисы пакета final, поэтому подменяется
     * PSR-18 клиент, а не сам сервис.
     */
    protected function fakeMaxApi(ResponseInterface ...$responses): MockHttpClient
    {
        $client = new MockHttpClient(array_values($responses));

        $this->app?->instance(ClientInterface::class, $client);

        return $client;
    }
}
