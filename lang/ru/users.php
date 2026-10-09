<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max пользователь',
        'plural_label'   => 'Max пользователи',
        'navigation_label' => 'Max пользователи',
    ],

    'table' => [
        'user_id'           => 'ID',
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
        'profile_checked_at' => 'Обновлён',
        'filter_is_bot'     => 'Тип',
        'filter_is_bot_user' => 'Пользователь',
        'filter_is_bot_bot'  => 'Бот',
        'filter_has_phone'  => 'С телефоном',
    ],

    'view' => [
        'title'              => 'Профиль пользователя',
        'user_id'            => 'ID пользователя',
        'first_name'         => 'Имя',
        'last_name'          => 'Фамилия',
        'username'           => 'Никнейм',
        'is_bot'             => 'Является ботом',
        'is_bot_yes'         => 'Да',
        'is_bot_no'          => 'Нет',
        'last_activity_time' => 'Последняя активность',
        'name'               => 'Отображаемое имя',
        'description'        => 'Описание',
        'avatar_url'         => 'Аватар',
        'full_avatar_url'    => 'Полный аватар',
        'phone'              => 'Телефон',
        'phone_verified_at'  => 'Телефон подтверждён',
        'phone_unverified'   => 'нет подтверждённого контакта',
        'email'              => 'Email',
        'profile_checked_at' => 'Профиль обновлён',
        'never_checked'      => 'ещё не синхронизирован',
        'chats'              => 'Чаты',
        'chat_name'          => 'Название',
        'chat_id'            => 'Chat ID',
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
    ],

];
