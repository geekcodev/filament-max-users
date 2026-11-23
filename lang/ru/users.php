<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max пользователь',
        'plural_label'   => 'Max пользователи',
        'navigation_label' => 'Max пользователи',
    ],

    'table' => [
        'user_id'           => 'ID пользователя',
        'first_name'        => 'Имя',
        'last_name'         => 'Фамилия',
        'username'          => 'Никнейм',
        'phone'             => 'Телефон',
        'phone_hint'        => 'Телефон получен из подтверждённого контакта',
        'email'             => 'Email',
        'is_bot'            => 'Бот',
        'last_activity_time' => 'Последняя активность',
        'chats_count'       => 'Чатов',
        'avatar'            => 'Аватар',
        'full_avatar_url'   => 'Полный аватар',
        'name'              => 'Отображаемое имя',
        'description'       => 'Описание',
        'phone_verified_at' => 'Телефон подтверждён',
        'profile_checked_at' => 'Профиль обновлён',
        'filter_is_bot'     => 'Тип пользователя',
        'filter_is_bot_user' => 'Пользователь',
        'filter_is_bot_bot'  => 'Бот',
        'filter_has_phone'  => 'С телефоном',
    ],

    'form' => [
        'context'     => 'Служебные данные',
        'profile'     => 'Профиль и контакты',
        'first_name'  => 'Имя',
        'last_name'   => 'Фамилия',
        'username'    => 'Никнейм',
        'name'        => 'Отображаемое имя',
        'description' => 'Описание',
        'phone'       => 'Телефон',
        'email'       => 'Email',
        'is_bot'      => 'Является ботом',
        'reset_phone_verification' => 'Сбросить подтверждение телефона',
        'reset_phone_verification_hint' => 'Номер останется, но отметка подтверждения будет удалена. При изменении номера отметка сбрасывается автоматически.',
    ],

    'pages' => [
        'edit_title' => 'Редактирование пользователя',
    ],

    'view' => [
        'user_id'            => 'ID пользователя',
        'first_name'         => 'Имя',
        'last_name'          => 'Фамилия',
        'username'           => 'Никнейм',
        'is_bot'             => 'Является ботом',
        'last_activity_time' => 'Последняя активность',
        'name'               => 'Отображаемое имя',
        'description'        => 'Описание',
        'avatar_url'         => 'Аватар',
        'phone'              => 'Телефон',
        'phone_verified_at'  => 'Телефон подтверждён',
        'phone_unverified'   => 'нет подтверждённого контакта',
        'email'              => 'Email',
        'profile_checked_at' => 'Профиль обновлён',
        'never_checked'      => 'ещё не синхронизирован',
        'chats'              => 'Чаты',
        'chat_name'          => 'Название',
        'chat_id'            => 'ID чата',
        'chat_type'          => 'Тип чата',
        'chat_last_activity_at' => 'Последняя активность',
    ],

    'actions' => [
        'refresh'          => 'Обновить из MAX',
        'refresh_heading'  => 'Обновление профиля',
        'refresh_description' => 'Запросить свежие данные профиля из MAX API. Профиль обновится, если у пользователя есть активный чат с ботом.',
        'refresh_submit'   => 'Обновить',
        'refresh_success'  => 'Профиль пользователя обновлён',
        'refresh_failure'  => 'Не удалось обновить профиль. Возможно, у пользователя нет активного чата с ботом.',
        'edit_success'     => 'Профиль пользователя сохранён',
        'delete'           => 'Удалить',
        'delete_heading'   => 'Удаление пользователя',
        'delete_description' => 'Запись будет удалена из локального реестра max_users вместе со связями с чатами. Данные пользователя в самом MAX не изменятся. Если на запись ссылается приложение, удаление будет отклонено.',
        'delete_submit'    => 'Удалить',
        'delete_success'   => 'Пользователь удалён из локального реестра',
        'delete_failure'   => 'Не удалось удалить пользователя: запись используется приложением.',
    ],

];
