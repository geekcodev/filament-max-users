<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Feature\Resources;

use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\FilamentMaxUsers\Resources\Pages\EditMaxUser;
use GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers;
use GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\FilamentMaxUsers\Tests\TestCase;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\LaravelMaxClient\Models\MaxChatUser;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Support\Facades\DB;
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
            'can_edit_users' => true,
            'can_delete_users' => true,
        ]);

        $this->actingAs($this->user);
    }

    public function test_resource_allows_editing_and_deletion_but_not_creation(): void
    {
        $record = MaxUser::create([
            'user_id' => 1099,
            'first_name' => 'Editable',
            'username' => 'editable',
            'is_bot' => false,
        ]);

        self::assertFalse(MaxUserResource::canCreate());
        self::assertTrue(MaxUserResource::canEdit($record));
        self::assertTrue(MaxUserResource::canDelete($record));
        self::assertNotContains('create', array_keys(MaxUserResource::getPages()));
        self::assertContains('edit', array_keys(MaxUserResource::getPages()));
    }

    public function test_editing_and_deletion_are_denied_without_permissions(): void
    {
        $this->user->update(['can_edit_users' => false, 'can_delete_users' => false]);

        $record = MaxUser::create([
            'user_id' => 1098,
            'first_name' => 'Denied',
            'is_bot' => false,
        ]);

        self::assertFalse(MaxUserResource::canEdit($record));
        self::assertFalse(MaxUserResource::canDelete($record));

        $this->get(MaxUserResource::getUrl('edit', ['record' => $record]))
            ->assertForbidden();
    }

    public function test_default_labels_follow_locale(): void
    {
        app()->setLocale('en');

        self::assertSame('Max user', MaxUserResource::getModelLabel());
        self::assertSame('Max users', MaxUserResource::getPluralModelLabel());
        self::assertSame('Max users', MaxUserResource::getNavigationLabel());

        app()->setLocale('ru');

        self::assertSame('Max пользователь', MaxUserResource::getModelLabel());
        self::assertSame('Max пользователи', MaxUserResource::getPluralModelLabel());
        self::assertSame('Max пользователи', MaxUserResource::getNavigationLabel());
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

        Livewire::test(ListMaxUsers::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(MaxUser::all());
    }

    public function test_list_row_opens_view_page(): void
    {
        $user = MaxUser::create([
            'user_id' => 1022,
            'first_name' => 'RowLink',
            'is_bot' => false,
        ]);

        $component = Livewire::test(ListMaxUsers::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(ListMaxUsers::class, $instance);

        self::assertSame(
            MaxUserResource::getUrl('view', ['record' => $user]),
            $instance->getTable()->getRecordUrl($user),
        );
    }

    public function test_list_shows_columns_in_expected_default_order(): void
    {
        $component = Livewire::test(ListMaxUsers::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(ListMaxUsers::class, $instance);

        $columns = $instance->getTable()->getVisibleColumns();

        self::assertSame(
            ['user_id', 'is_bot', 'avatar_url', 'first_name', 'last_name', 'phone', 'chat_links_count'],
            array_keys($columns),
        );
    }

    public function test_list_exposes_every_column_as_toggleable(): void
    {
        $component = Livewire::test(ListMaxUsers::class);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(ListMaxUsers::class, $instance);

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

        Livewire::test(ListMaxUsers::class)
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

        Livewire::test(ListMaxUsers::class)
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

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
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

        $component = Livewire::test(ViewMaxUser::class, ['record' => $user->user_id]);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(ViewMaxUser::class, $instance);

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

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
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

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
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

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
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

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
            ->callAction('refresh')
            ->assertNotified();

        $this->assertSame('FailRefresh', $user->fresh()?->first_name);
    }

    public function test_edit_page_updates_profile_and_contact_fields(): void
    {
        $user = MaxUser::create([
            'user_id' => 1011,
            'first_name' => 'Before',
            'is_bot' => false,
        ]);

        Livewire::test(EditMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->fillForm([
                'first_name' => 'After',
                'last_name' => 'Фамилия',
                'username' => 'after',
                'name' => 'After Name',
                'description' => 'Описание профиля',
                'phone' => '+79990001122',
                'email' => 'after@example.com',
                'is_bot' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect(MaxUserResource::getUrl('index'));

        $fresh = $user->fresh();

        $this->assertNotNull($fresh);
        $this->assertSame('After', $fresh->first_name);
        $this->assertSame('Фамилия', $fresh->last_name);
        $this->assertSame('after', $fresh->username);
        $this->assertSame('After Name', $fresh->name);
        $this->assertSame('Описание профиля', $fresh->description);
        $this->assertSame('+79990001122', $fresh->phone);
        $this->assertSame('after@example.com', $fresh->email);
        $this->assertTrue($fresh->is_bot);
    }

    public function test_edit_form_exposes_only_editable_fields(): void
    {
        $user = MaxUser::create([
            'user_id' => 1012,
            'first_name' => 'Fields',
            'phone_verified_at' => now(),
            'is_bot' => false,
        ]);

        $component = Livewire::test(EditMaxUser::class, ['record' => $user->user_id]);
        $component->assertSuccessful();

        $instance = $component->instance();
        self::assertInstanceOf(EditMaxUser::class, $instance);

        $schema = $instance->getSchema('form');

        self::assertNotNull($schema);

        $names = [];

        foreach ($schema->getComponents() as $field) {
            if ($field instanceof Field) {
                $names[] = $field->getName();
            }
        }

        self::assertSame(
            [
                'first_name', 'last_name', 'username', 'name', 'description', 'phone',
                'reset_phone_verification', 'email', 'is_bot',
            ],
            $names,
        );
    }

    public function test_edit_page_can_reset_phone_verification(): void
    {
        $user = MaxUser::create([
            'user_id' => 1020,
            'first_name' => 'Verified',
            'phone' => '+79990000001',
            'phone_verified_at' => now(),
            'is_bot' => false,
        ]);

        Livewire::test(EditMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->fillForm(['reset_phone_verification' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $user->fresh();

        $this->assertNotNull($fresh);
        $this->assertNull($fresh->phone_verified_at);
        $this->assertSame('+79990000001', $fresh->phone);
    }

    public function test_edit_page_resets_verification_when_phone_changes(): void
    {
        $user = MaxUser::create([
            'user_id' => 1021,
            'first_name' => 'ChangedPhone',
            'phone' => '+79990000001',
            'phone_verified_at' => now(),
            'is_bot' => false,
        ]);

        Livewire::test(EditMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->fillForm(['phone' => '+79990000002'])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $user->fresh();

        $this->assertNotNull($fresh);
        $this->assertSame('+79990000002', $fresh->phone);
        $this->assertNull($fresh->phone_verified_at);
    }

    public function test_edit_and_delete_actions_are_visible_with_permissions(): void
    {
        $user = MaxUser::create([
            'user_id' => 1013,
            'first_name' => 'Actions',
            'is_bot' => false,
        ]);

        Livewire::test(ListMaxUsers::class)
            ->assertSuccessful()
            ->assertTableActionVisible('edit', $user)
            ->assertTableActionVisible('delete', $user);

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->assertActionVisible('edit')
            ->assertActionVisible('delete');
    }

    public function test_edit_and_delete_actions_are_hidden_without_permissions(): void
    {
        $this->user->update(['can_edit_users' => false, 'can_delete_users' => false]);

        $user = MaxUser::create([
            'user_id' => 1014,
            'first_name' => 'Hidden',
            'is_bot' => false,
        ]);

        Livewire::test(ListMaxUsers::class)
            ->assertSuccessful()
            ->assertTableActionHidden('edit', $user)
            ->assertTableActionHidden('delete', $user);

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful()
            ->assertActionHidden('edit')
            ->assertActionHidden('delete');
    }

    public function test_delete_action_removes_user_and_chat_links(): void
    {
        $user = MaxUser::create([
            'user_id' => 1015,
            'first_name' => 'Deletable',
            'is_bot' => false,
        ]);

        $this->linkUserToChat($user->user_id, 4010);

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
            ->callAction('delete')
            ->assertNotified()
            ->assertRedirect(MaxUserResource::getUrl('index'));

        $this->assertDatabaseMissing('max_users', ['user_id' => 1015]);
        $this->assertDatabaseMissing('max_chat_users', ['user_id' => 1015]);
    }

    public function test_delete_action_rolls_back_when_host_application_references_user(): void
    {
        $user = MaxUser::create([
            'user_id' => 1016,
            'first_name' => 'Referenced',
            'is_bot' => false,
        ]);

        $this->linkUserToChat($user->user_id, 4011);

        DB::table('host_profiles')->insert([
            'max_user_id' => $user->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(ViewMaxUser::class, ['record' => $user->user_id])
            ->callAction('delete')
            ->assertNotified();

        $this->assertDatabaseHas('max_users', ['user_id' => 1016]);
        $this->assertDatabaseHas('max_chat_users', ['user_id' => 1016]);
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
