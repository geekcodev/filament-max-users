<?php

declare(strict_types=1);

return [

    // Права (проверяются через $user->can(...) — совместимо со spatie/laravel-permission и Gate).
    'permissions' => [
        'users.view'   => env('FILAMENT_MAX_USERS_PERMISSION_USERS_VIEW', 'users.view'),
        'users.manage' => env('FILAMENT_MAX_USERS_PERMISSION_USERS_MANAGE', 'users.manage'),
        'chats.view'   => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_VIEW', 'chats.view'),
        'chats.manage' => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_MANAGE', 'chats.manage'),
        'chats.delete' => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_DELETE', 'chats.delete'),
    ],

    // Модели (по умолчанию из laravel-max-client).
    'users_model' => GeekCo\LaravelMaxClient\Models\MaxUser::class,
    'chats_model' => GeekCo\LaravelMaxClient\Models\MaxChat::class,

    // UI ресурса «Max пользователи».
    'ui' => [
        'navigation_group' => env('FILAMENT_MAX_USERS_NAVIGATION_GROUP', 'Max'),
        'users' => [
            'navigation_icon'  => 'heroicon-o-users',
            'navigation_sort'  => 1,
            'navigation_label' => null,
            'label'            => 'Max пользователь',
            'plural_label'     => 'Max пользователи',
            'slug'             => 'max-users',
        ],
        'chats' => [
            'navigation_icon'  => 'heroicon-o-chat-bubble-left-right',
            'navigation_sort'  => 2,
            'navigation_label' => null,
            'label'            => 'Max чат',
            'plural_label'     => 'Max чаты',
            'slug'             => 'max-chats',
        ],
    ],

];
