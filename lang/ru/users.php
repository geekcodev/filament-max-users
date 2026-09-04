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
        'username'          => 'Username',
        'is_bot'            => 'Бот',
        'last_activity_time' => 'Последняя активность',
        'chats_count'       => 'Чатов',
        'avatar'            => 'Аватар',
        'filter_is_bot'     => 'Тип',
        'filter_is_bot_user' => 'Пользователь',
        'filter_is_bot_bot'  => 'Бот',
    ],

    'view' => [
        'title'              => 'Профиль пользователя',
        'user_id'            => 'ID пользователя',
        'first_name'         => 'Имя',
        'last_name'          => 'Фамилия',
        'username'           => 'Username',
        'is_bot'             => 'Является ботом',
        'is_bot_yes'         => 'Да',
        'is_bot_no'          => 'Нет',
        'last_activity_time' => 'Последняя активность',
        'name'               => 'Отображаемое имя',
        'description'        => 'Описание',
        'avatar_url'         => 'URL аватара',
        'full_avatar_url'    => 'URL полного аватара',
        'phone'              => 'Телефон',
        'email'              => 'Email',
        'profile_checked_at' => 'Последняя синхронизация',
        'chats_count'        => 'Количество чатов',
    ],

    'actions' => [
        'refresh'          => 'Обновить из MAX',
        'refresh_heading'  => 'Обновление профиля',
        'refresh_description' => 'Запросить актуальные данные профиля пользователя из MAX API. Профиль будет обновлён, если у пользователя есть активный чат с ботом.',
        'refresh_submit'   => 'Обновить',
        'refresh_success'  => 'Профиль пользователя обновлён',
        'refresh_failure'  => 'Не удалось обновить профиль. Возможно, у пользователя нет активного чата с ботом.',
    ],

];
