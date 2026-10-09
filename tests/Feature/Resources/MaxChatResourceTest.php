<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Feature\Resources;

use Filament\Infolists\Components\Entry;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\FilamentMaxUsers\Tests\TestCase;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\LaravelMaxClient\Models\MaxChatUser;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\MaxPhpClient\Enum\ChatType;
use GuzzleHttp\Psr7\Response;
use Livewire\Livewire;

class MaxChatResourceTest extends TestCase
{
    private TestUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = TestUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'can_view_chats' => true,
            'can_manage_chats' => true,
            'can_delete_chats' => true,
        ]);

        $this->actingAs($this->user);
    }

    public function test_resource_is_accessible(): void
    {
        $this->get(MaxChatResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_resource_is_inaccessible_without_permission(): void
    {
        $this->user->update(['can_view_chats' => false]);

        $this->get(MaxChatResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_list_displays_chats(): void
    {
        $chat = $this->makeChat(3001, title: 'Релизы');

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$chat])
            ->assertTableColumnStateSet('displayName', 'Релизы', $chat);
    }

    public function test_list_shows_columns_in_expected_default_order(): void
    {
        $component = Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class, $instance);

        $columns = $instance->getTable()->getVisibleColumns();

        self::assertSame(
            ['chat_id', 'chat_type', 'status', 'displayName', 'chat_users_count', 'last_activity_at'],
            array_keys($columns),
        );
    }

    public function test_list_exposes_every_column_as_toggleable(): void
    {
        $component = Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class, $instance);

        $columns = $instance->getTable()->getColumns();

        self::assertSame(
            [
                'chat_id', 'chat_type', 'status', 'icon_url', 'displayName', 'title', 'description',
                'link', 'chat_users_count', 'last_activity_at', 'chat_checked_at',
            ],
            array_keys($columns),
        );

        foreach ($columns as $column) {
            self::assertTrue($column->isToggleable(), $column->getName());
        }
    }

    public function test_list_falls_back_to_linked_user_name_for_dialog(): void
    {
        $chat = $this->makeChat(3002, chatType: ChatType::Dialog);

        MaxChatUser::create([
            'chat_id' => $chat->chat_id,
            'user_id' => 2001,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class)
            ->assertSuccessful()
            ->assertTableColumnStateSet('displayName', 'ChatUser', $chat);
    }

    public function test_list_shows_participants_count(): void
    {
        $empty = $this->makeChat(3012);
        $chat = $this->makeChat(3003);

        MaxChatUser::create(['chat_id' => $chat->chat_id, 'user_id' => 2001]);
        MaxChatUser::create(['chat_id' => $chat->chat_id, 'user_id' => 2002]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class)
            ->assertSuccessful()
            ->assertTableColumnStateSet('chat_users_count', 2, $chat)
            ->assertTableColumnStateSet('chat_users_count', 0, $empty)
            ->sortTable('chat_users_count')
            ->assertCanSeeTableRecords([$empty, $chat], inOrder: false);
    }

    public function test_list_filters_chats_by_type(): void
    {
        $group = $this->makeChat(3004, chatType: ChatType::Chat);
        $channel = $this->makeChat(3005, chatType: ChatType::Channel);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class)
            ->assertSuccessful()
            ->filterTable('chat_type', ChatType::Channel->value)
            ->assertCanSeeTableRecords([$channel])
            ->assertCanNotSeeTableRecords([$group]);
    }

    public function test_view_page_displays_chat_metadata(): void
    {
        $chat = $this->makeChat(3006, title: 'Релиз 1.2.0', link: 'https://max.ru/chat/3006');

        MaxChatUser::create(['chat_id' => $chat->chat_id, 'user_id' => 2001]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->assertSuccessful()
            ->assertSee('Релиз 1.2.0')
            ->assertSee('https://max.ru/chat/3006')
            ->assertSee('ChatUser');
    }

    public function test_view_page_shows_entries_in_expected_order(): void
    {
        $chat = $this->makeChat(3013);

        $component = Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id]);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, $instance);

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
                'chat_id', 'icon_url', 'chat_type', 'status', 'displayName', 'description', 'link',
                'last_activity_at', 'chat_checked_at', 'chatUsers',
            ],
            $names,
        );
    }

    public function test_refresh_metadata_action_is_hidden_without_permission(): void
    {
        $this->user->update(['can_manage_chats' => false]);

        $chat = $this->makeChat(3007);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->assertSuccessful()
            ->assertActionHidden('refreshChat');
    }

    public function test_refresh_metadata_action_writes_chat_metadata_from_max(): void
    {
        $chat = $this->makeChat(3008);

        $http = $this->fakeMaxApi($this->chatResponse(3008, 'chat', [
            'title' => 'Название из MAX',
            'description' => 'Описание из MAX',
            'link' => 'https://max.ru/release',
            'icon' => ['url' => 'https://max.ru/icon.png'],
        ]));

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->callAction('refreshChat')
            ->assertNotified();

        $fresh = $chat->fresh();

        $this->assertNotNull($fresh);
        $this->assertSame('Название из MAX', $fresh->title);
        $this->assertSame('Описание из MAX', $fresh->description);
        $this->assertSame('https://max.ru/release', $fresh->link);
        $this->assertSame('https://max.ru/icon.png', $fresh->icon_url);
        $this->assertSame(1, $http->callCount);
        $this->assertSame(1, MaxChat::query()->count());
    }

    public function test_refresh_metadata_action_parses_group_participants_map(): void
    {
        $chat = $this->makeChat(3011);

        $http = $this->fakeMaxApi($this->chatResponse(3011, 'chat', [
            'title' => 'Группа из MAX',
            'participants' => ['2001' => 1700000000000, '2002' => 1700000005000],
            'participants_count' => 2,
        ]));

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->callAction('refreshChat')
            ->assertNotified();

        $this->assertSame('Группа из MAX', $chat->fresh()?->title);
        $this->assertSame(1, $http->callCount);
    }

    public function test_refresh_metadata_action_reports_api_failure(): void
    {
        $chat = $this->makeChat(3009);

        $this->fakeMaxApi(new Response(404, [], '{"code":"chat.not.found","message":"not found"}'));

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->callAction('refreshChat')
            ->assertNotified();

        $this->assertNull($chat->fresh()?->title);
    }

    public function test_delete_action_removes_chat_and_its_links(): void
    {
        $chat = $this->makeChat(3010);

        MaxChatUser::create(['chat_id' => $chat->chat_id, 'user_id' => 2001]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->callAction('delete')
            ->assertNotified();

        $this->assertDatabaseMissing('max_chats', ['chat_id' => 3010]);
        $this->assertDatabaseMissing('max_chat_users', ['chat_id' => 3010]);
    }

    public function test_delete_action_is_hidden_without_permission(): void
    {
        $this->user->update(['can_delete_chats' => false]);

        $chat = $this->makeChat(3011);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->chat_id])
            ->assertSuccessful()
            ->assertActionHidden('delete');

        $this->assertDatabaseHas('max_chats', ['chat_id' => 3011]);
    }

    public function test_resource_is_read_only(): void
    {
        $record = $this->makeChat(3099);

        self::assertFalse(MaxChatResource::canCreate());
        self::assertFalse(MaxChatResource::canEdit($record));
        self::assertFalse(MaxChatResource::canDelete($record));
        self::assertNotContains('create', array_keys(MaxChatResource::getPages()));
        self::assertNotContains('edit', array_keys(MaxChatResource::getPages()));
    }

    private function makeChat(
        int $chatId,
        ?ChatType $chatType = ChatType::Chat,
        ?string $title = null,
        ?string $link = null,
    ): MaxChat {
        MaxUser::firstOrCreate(
            ['user_id' => 2001],
            [
                'first_name' => 'ChatUser',
                'username' => 'chatuser',
                'is_bot' => false,
            ],
        );

        return MaxChat::create([
            'chat_id' => $chatId,
            'status' => MaxChatStatus::Active,
            'chat_type' => $chatType,
            'title' => $title,
            'link' => $link,
        ]);
    }
}
