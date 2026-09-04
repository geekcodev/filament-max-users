<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max чат',
        'plural_label'   => 'Max чаты',
        'navigation_label' => 'Max чаты',
    ],

    'table' => [
        'id'              => 'ID',
        'user_id'         => 'Пользователь',
        'chat_id'         => 'Chat ID',
        'chat_type'       => 'Тип',
        'status'          => 'Статус',
        'last_activity_at' => 'Последняя активность',
        'user_name'       => 'Имя пользователя',
    ],

    'chat_type' => [
        'dialog'  => 'Диалог',
        'group'   => 'Группа',
        'channel' => 'Канал',
        'unknown' => 'Неизвестно',
    ],

    'view' => [
        'title'              => 'Детали чата',
        'id'                 => 'ID записи',
        'user_id'            => 'Пользователь',
        'chat_id'            => 'Chat ID',
        'chat_type'          => 'Тип чата',
        'status'             => 'Статус',
        'last_activity_at'   => 'Последняя активность',
        'user_name'          => 'Имя пользователя',
        'api_details'        => 'Данные из MAX API',
        'api_unavailable'    => 'Данные из MAX API недоступны',
    ],

    'actions' => [
        'delete'          => 'Удалить запись',
        'delete_heading'  => 'Удаление записи чата',
        'delete_description' => 'Удалить запись чата из локального реестра. Данные в самом MAX не затрагиваются.',
        'delete_submit'   => 'Удалить',
        'delete_success'  => 'Запись чата удалена',
    ],

];
