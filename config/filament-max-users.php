<?php

declare(strict_types=1);

return [

    // Права и модели живут в laravel-max-client: Policy-классы MaxUserPolicy /
    // MaxChatPolicy, конфиг laravel-max-client.permissions и users.model /
    // chats.model. Здесь только UI-настройки плагина.

    // UI ресурса «Max пользователи».
    'ui' => [
        'navigation_group' => 'Max',
        'users' => [
            'navigation_icon'  => 'heroicon-o-users',
            'navigation_sort'  => 1,
            'navigation_label' => null,
            'label'            => null,
            'plural_label'     => null,
            'slug'             => 'max-users',
        ],
        'chats' => [
            'navigation_icon'  => 'heroicon-o-chat-bubble-left-right',
            'navigation_sort'  => 2,
            'navigation_label' => null,
            'label'            => null,
            'plural_label'     => null,
            'slug'             => 'max-chats',
        ],
    ],

];
