<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Feature\Resources;

use Filament\Infolists\Components\Entry;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\FilamentMaxUsers\Tests\TestCase;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\LaravelMaxClient\Models\MaxChatUser;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Livewire\Livewire;

class MaxUserResourceTest extends TestCase
{
    private TestUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = TestUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'can_view_users' => true,
            'can_manage_users' => true,
        ]);

        $this->actingAs($this->user);
    }

    public function test_resource_is_read_only(): void
    {
        $record = MaxUser::create([
            'user_id' => 1099,
            'first_name' => 'ReadOnly',
            'username' => 'readonly',
            'is_bot' => false,
        ]);

        self::assertFalse(MaxUserResource::canCreate());
        self::assertFalse(MaxUserResource::canEdit($record));
        self::assertFalse(MaxUserResource::canDelete($record));
        self::assertNotContains('create', array_keys(MaxUserResource::getPages()));
        self::assertNotContains('edit', array_keys(MaxUserResource::getPages()));
    }

    public function test_resource_is_accessible(): void
    {
        $this->get(MaxUserResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_resource_is_inaccessible_without_permission(): void
    {
        $this->user->update(['can_view_users' => false]);

        $this->get(MaxUserResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_list_displays_users(): void
    {
        MaxUser::create([
            'user_id' => 1001,
            'first_name' => 'John',
            'username' => 'john',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(MaxUser::all());
    }

    public function test_list_shows_columns_in_expected_default_order(): void
    {
        $component = Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class, $instance);

        $columns = $instance->getTable()->getVisibleColumns();

        self::assertSame(
            ['user_id', 'is_bot', 'avatar_url', 'first_name', 'last_name', 'phone', 'chat_links_count'],
            array_keys($columns),
        );
    }

    public function test_list_exposes_every_column_as_toggleable(): void
    {
        $component = Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class, $instance);

        $columns = $instance->getTable()->getColumns();

        self::assertSame(
            [
                'user_id', 'is_bot', 'avatar_url', 'full_avatar_url', 'first_name', 'last_name',
                'name', 'description', 'username', 'phone', 'phone_verified_at', 'email',
                'chat_links_count', 'last_activity_time', 'profile_checked_at',
            ],
            array_keys($columns),
        );

        foreach ($columns as $column) {
            self::assertTrue($column->isToggleable(), $column->getName());
        }
    }

    public function test_list_shows_contact_data_and_chats_count(): void
    {
        $user = MaxUser::create([
            'user_id' => 1002,
            'first_name' => 'Jane',
            'username' => 'jane',
            'is_bot' => false,
            'phone' => '+79991234567',
            'email' => 'jane@example.com',
        ]);

        $this->linkUserToChat($user->user_id, 4001);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class)
            ->assertSuccessful()
            ->assertTableColumnStateSet('phone', '+79991234567', $user)
            ->assertTableColumnStateSet('email', 'jane@example.com', $user)
            ->assertTableColumnStateSet('chat_links_count', 1, $user);
    }

    public function test_list_filters_users_with_phone(): void
    {
        $withPhone = MaxUser::create([
            'user_id' => 1003,
            'first_name' => 'WithPhone',
            'is_bot' => false,
            'phone' => '+79990000000',
        ]);

        $withoutPhone = MaxUser::create([
            'user_id' => 1004,
            'first_name' => 'WithoutPhone',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers::class)
            ->assertSuccessful()
            ->filterTable('has_phone')
            ->assertCanSeeTableRecords([$withPhone])
            ->assertCanNotSeeTableRecords([$withoutPhone]);
    }

    public function test_view_page_displays_user(): void
    {
        $user = MaxUser::create([
            'user_id' => 1005,
            'first_name' => 'Jane',
            'username' => 'jane',
            'is_bot' => false,
            'phone' => '+79991234567',
            'phone_verified_at' => now(),
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->assertSee('+79991234567');
    }

    public function test_view_page_shows_entries_in_expected_order(): void
    {
        $user = MaxUser::create([
            'user_id' => 1010,
            'first_name' => 'Ordered',
            'is_bot' => false,
        ]);

        $component = Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id]);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, $instance);

        $schema = $instance->getSchema('infolist');
        self::assertNotNull($schema);

        $names = [];

        foreach ($schema->getComponents() as $entry) {
            if ($entry instanceof Entry) {
                $names[] = $entry->getName();
            }
        }

        self::assertSame(
            [
                'user_id', 'is_bot', 'avatar_url', 'username', 'first_name', 'last_name',
                'name', 'description', 'phone', 'phone_verified_at', 'email',
                'last_activity_time', 'profile_checked_at', 'maxChats',
            ],
            $names,
        );
    }

    public function test_view_page_lists_chats_of_user(): void
    {
        $user = MaxUser::create([
            'user_id' => 1006,
            'first_name' => 'Chatted',
            'is_bot' => false,
        ]);

        $this->linkUserToChat($user->user_id, 4002, 'Проектный чат');

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->assertSee('Проектный чат');
    }

    public function test_refresh_action_is_hidden_without_permission(): void
    {
        $this->user->update(['can_manage_users' => false]);

        $user = MaxUser::create([
            'user_id' => 1007,
            'first_name' => 'NoPerm',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->assertActionHidden('refresh');
    }

    public function test_refresh_action_updates_profile_from_max(): void
    {
        $user = MaxUser::create([
            'user_id' => 1008,
            'first_name' => 'Refresh',
            'is_bot' => false,
        ]);

        $this->linkUserToChat($user->user_id, 4003);

        $http = $this->fakeMaxApi($this->chatMemberResponse(1008, [
            'first_name' => 'Обновлённое имя',
            'name' => 'Обновлённое имя Пользователь',
        ]));

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->callAction('refresh')
            ->assertNotified();

        $fresh = $user->fresh();

        $this->assertNotNull($fresh);
        $this->assertSame('Обновлённое имя', $fresh->first_name);
        $this->assertSame('Обновлённое имя Пользователь', $fresh->name);
        $this->assertNotNull($fresh->profile_checked_at);
        $this->assertSame(1, $http->callCount);
    }

    public function test_refresh_action_reports_failure(): void
    {
        $user = MaxUser::create([
            'user_id' => 1009,
            'first_name' => 'FailRefresh',
            'is_bot' => false,
        ]);

        $this->fakeMaxApi(new \GuzzleHttp\Psr7\Response(404, [], '{"code":"not.found","message":"not found"}'));

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->callAction('refresh')
            ->assertNotified();

        $this->assertSame('FailRefresh', $user->fresh()?->first_name);
    }

    private function linkUserToChat(int $userId, int $chatId, ?string $title = null): void
    {
        MaxUser::firstOrCreate(
            ['user_id' => $userId],
            ['first_name' => 'Linked', 'is_bot' => false],
        );

        MaxChat::firstOrCreate(
            ['chat_id' => $chatId],
            [
                'status' => MaxChatStatus::Active,
                // Группа: displayName берётся из title, а профиль обновляется
                // через getChatMembers. Для диалогов путь другой — см.
                // MaxChatResourceTest::test_list_falls_back_to_linked_user_name_for_dialog.
                'chat_type' => ChatType::Chat,
                'title' => $title ?? 'Чат '.PHP_INT_MAX,
            ],
        );

        MaxChatUser::firstOrCreate([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }
}
