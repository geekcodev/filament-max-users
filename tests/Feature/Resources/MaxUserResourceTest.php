<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Feature\Resources;

use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\FilamentMaxUsers\Tests\Fixtures\TestUser;
use GeekCo\FilamentMaxUsers\Tests\TestCase;
use GeekCo\LaravelMaxClient\Models\MaxUser;
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
            ->assertSuccessful();
    }

    public function test_view_page_displays_user(): void
    {
        $user = MaxUser::create([
            'user_id' => 1002,
            'first_name' => 'Jane',
            'username' => 'jane',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful();
    }

    public function test_view_page_shows_chats_count(): void
    {
        $user = MaxUser::create([
            'user_id' => 1003,
            'first_name' => 'Test',
            'username' => 'test',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful();
    }

    public function test_refresh_action_is_visible_with_permission(): void
    {
        $user = MaxUser::create([
            'user_id' => 1004,
            'first_name' => 'Refresh',
            'username' => 'refresh',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful();
    }

    public function test_refresh_action_is_invisible_without_permission(): void
    {
        $this->user->update(['can_manage_users' => false]);

        $user = MaxUser::create([
            'user_id' => 1005,
            'first_name' => 'NoPerm',
            'username' => 'noperm',
            'is_bot' => false,
        ]);

        Livewire::test(\GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser::class, ['record' => $user->user_id])
            ->assertSuccessful();
    }
}
