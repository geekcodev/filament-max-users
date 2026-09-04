<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Feature\Resources;

use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\FilamentMaxUsers\Tests\TestCase;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\LaravelMaxClient\Models\MaxUser;
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
        $maxUser = MaxUser::create([
            'user_id' => 2001,
            'first_name' => 'ChatUser',
            'username' => 'chatuser',
            'is_bot' => false,
        ]);

        MaxChat::create([
            'user_id' => $maxUser->user_id,
            'chat_id' => 3001,
            'status' => MaxChatStatus::Active,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats::class)
            ->assertSuccessful();
    }

    public function test_view_page_displays_chat(): void
    {
        $maxUser = MaxUser::create([
            'user_id' => 2002,
            'first_name' => 'ViewChat',
            'username' => 'viewchat',
            'is_bot' => false,
        ]);

        $chat = MaxChat::create([
            'user_id' => $maxUser->user_id,
            'chat_id' => 3002,
            'status' => MaxChatStatus::Active,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->id])
            ->assertSuccessful();
    }

    public function test_delete_action_is_inaccessible_without_permission(): void
    {
        $this->user->update(['can_delete_chats' => false]);

        $maxUser = MaxUser::create([
            'user_id' => 2003,
            'first_name' => 'DelChat',
            'username' => 'delchat',
            'is_bot' => false,
        ]);

        $chat = MaxChat::create([
            'user_id' => $maxUser->user_id,
            'chat_id' => 3003,
            'status' => MaxChatStatus::Active,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat::class, ['record' => $chat->id])
            ->assertSuccessful();
    }
}
