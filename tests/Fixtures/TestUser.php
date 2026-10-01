<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $name
 * @property string $email
 * @property bool $can_view_users
 * @property bool $can_manage_users
 * @property bool $can_view_chats
 * @property bool $can_manage_chats
 * @property bool $can_delete_chats
 */
class TestUser extends BaseUser
{
    use Notifiable;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'can_view_users',
        'can_manage_users',
        'can_view_chats',
        'can_manage_chats',
        'can_delete_chats',
    ];

    protected function casts(): array
    {
        return [
            'can_view_users' => 'boolean',
            'can_manage_users' => 'boolean',
            'can_view_chats' => 'boolean',
            'can_manage_chats' => 'boolean',
            'can_delete_chats' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
